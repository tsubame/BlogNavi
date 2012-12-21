<?php
App::uses('ComponentCollection', 'Controller');
App::uses('CurlComponent', 'Controller/Component');
App::uses('RssUtilComponent', 'Controller/Component');

/**
 * SitesControllerのregisterFromRankアクション
 *
 * ブログランキングのページからサイトを登録する
 *
 * ブログ登録の仕様
 *
 *  ・定期的に実行
 *  ・登録済みのものは登録しない
 *  ・削除ボタンで削除 deleted を trueに
 *	・カテゴリ未分類の列を付ける
 *
 * 依存クラス
 * ・Component/RssUtilComponent
 * ・Component/CurlComponent
 * ・ComponentCollection
 *
 * エラー
 * ・？？
 *
 */
class SiteRegisterFromRankAction extends AppModel {

	/**
	 * テーブルの使用
	 *
	 * @var bool
	 */
	public $useTable = false;

	/**
	 * CurlComponent
	 *
	 * @var object CurlComponent
	 */
	private $Curl;

	/**
	 * RssUtilComponent
	 *
	 * @var object RssUtilComponent
	 */
	private $RssUtil;

	/**
	 * FC2ブログランキングのURL
	 *
	 * @var string
	 */
	const FC2_RANK_URL = 'http://blog.fc2.com/subgenre/250/';

	/**
	 * ライブドアブログランキングのURL
	 *
	 * @var string
	 */
	const LIVEDOOR_RANK_URL = 'http://blog.livedoor.com/category/9/';

	/**
	 * ライブドアブログランキングのURL
	 *
	 * @var string
	 */
	const AMEBA_RANK_URL = 'http://ranking.ameba.jp/gr_soccer';


// アメーバからも登録したい

	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		parent::__construct();

		$collection    = new ComponentCollection();
		$this->RssUtil = new RssUtilComponent($collection);
		$this->Curl    = new CurlComponent($collection);
	}

	/**
	 * 処理実行
	 *
	 */
	public function exec() {
		// FC2、ライブドアのランキングサイトを取得

		$fc2Sites = $this->getFc2RankSites();
		$ldSites  = $this->getLivedoorRankSites();
		$amSites  = $this->getAmebaRankSites();
		$sites    = array_merge($fc2Sites, $ldSites);
		$sites    = array_merge($sites, $amSites);
		//$sites = $amSites;

		// フィードURL、その他の値を設定
		foreach ($sites as $i => $site) {
			// フィードURLを取得
			$sites[$i]['feed_url'] = $this->RssUtil->getFeedUrlFromSiteUrl($site['url']);

			if ($sites[$i]['feed_url'] == false) {
				debug("フィードURLを取得できませんでした  \n{$site['url']}");
			}

			$sites[$i]['is_registered'] = false;
			// カテゴリIDをブログのカテゴリIDに
			$sites[$i]['category_id']   = Configure::read('Category.blogId');
		}

		// カテゴライズ
		$this->autoCategorize($sites);

		$siteModel = ClassRegistry::init('Site');
		// サイトを登録
		foreach ($sites as $site) {
			$siteModel->saveIfNotExists($site);
		}
	}

	/**
	 * FC2ブログランキングにランクしているサイトを取得
	 *
	 * ランキングサイトのhtmlを取得して、
	 * その中から正規表現をサイト名とURLを抽出する
	 *
	 * 1ページに30件のサイトがあるので30件登録する
	 *
	 * @return array $sites
	 * 					 $sites => array('name' => サイト名, 'url' => URL)
	 */
	protected function getFc2RankSites() {
		// サイト抽出用の正規表現パターン
		$tagPattern    = '/<a[^>]+[\w]+\.blog[\d]*\.fc2\.com\/[^>]+title[^>]+>/is';
		$namePattern   = '/title[^"]+"([^"]+)"/is';
		$urlPattern    = '/http:\/\/[\w]+\.blog[\d]*\.fc2\.com\//';
		// 登録する件数
		$registerCount = 30;

		// HTMLデータ取得
		$html = $this->Curl->getContent(self::FC2_RANK_URL);

		$sites = array();
		// 正規表現でサイトが書かれたタグを検索
		if (preg_match_all($tagPattern, $html, $tags)) {
			foreach ($tags[0] as $i => $tag) {
				if ($registerCount < $i) {
					break;
				}

				// サイト名を抽出
				if (preg_match($namePattern, $tag, $names)){
					$sites[$i]['name'] = $names[1];
				} else {
					continue;
				}
				// URLを抽出
				if (preg_match($urlPattern, $tag, $urlMatchs)){
					$sites[$i]['url'] = $urlMatchs[0];
				}

				$sites[$i]['registered_from']  = 'fc2';
			}
		}

		return $sites;
	}

	/**
	 * ライブドアブログランキングにランクしているサイトを取得
	 *
	 * ランキングサイトのhtmlを取得して、
	 * その中から正規表現をサイト名とURLを抽出する
	 *
	 * 1ページに50件のサイトがあるので50件登録する
	 *
	 * @return array $sites
	 * 					$sites => array('name' => サイト名, 'url' => URL)
	 */
	protected function getLivedoorRankSites() {
		//正規表現パターン
		$tagPattern    = '/<h3[\s]+class\="ttl">.+?<\/h3>/is';
		$urlPattern    = '/http:\/\/[\w\/\.\-_]+/is';
		$namePattern   = '/位">([^<]+)<\/a/is';
		$registerCount = 50;

		// HTMLデータ取得
		$html = $this->Curl->getContent(self::LIVEDOOR_RANK_URL);

		$sites = array();
		// 正規表現でサイトを検索
		if (preg_match_all($tagPattern, $html, $tags)) {

			foreach ($tags[0] as $i => $tag) {
				if ($registerCount < $i) {
					break;
				}

				// サイト名を取得
				if (preg_match($namePattern, $tag, $names)){
					$sites[$i]['name'] = $names[1];
				} else {
					continue;
				}
				// URLを取得
				if (preg_match($urlPattern, $tag, $urlMatchs)){
					$sites[$i]['url'] = $urlMatchs[0];
				}

				$sites[$i]['registered_from']  = 'livedoor';
			}
		}

		return $sites;
	}

	/**
	 * アメーバブログから登録
	 *
	 * @return array $sites
	 */
	protected function getAmebaRankSites() {
		//正規表現パターン
		$tagPattern    = '/<dd class="title">.+?<\/dd>/is';
		$namePattern   = '/http:\/\/[\w\.\-\/_=?&@:]+">([^<]+)<\/a/is';
		$registerCount = 20;

		// HTMLデータ取得
		$html = $this->Curl->getContent(self::AMEBA_RANK_URL);

		$sites = array();
		// 正規表現でサイトを検索
		if (preg_match_all($tagPattern, $html, $tags)) {
			foreach ($tags[0] as $i => $tag) {
				if ($registerCount < $i) {
					break;
				}

				// サイト名を取得
				if (preg_match($namePattern, $tag, $names)){
					$sites[$i]['name'] = $names[1];
				} else {
					continue;
				}
				// URLを取得
				if (preg_match(Configure::read('urlPattern'), $tag, $urlMatchs)){
					$sites[$i]['url'] = $urlMatchs[0];
				}

				$sites[$i]['registered_from']  = 'ameba';
			}
		}

		return $sites;
	}

	/**
	 * 2chまとめブログをカテゴライズ
	 *
	 * RSSフィードのサマリーを見て2chまとめブログと判断できたら
	 * カテゴリIDを2chまとめブログのものに
	 *
	 * @param array &$sites
	 */
	public function autoCategorize(&$sites) {
		// 2chまとめブログの記事の正規表現パターン 日付とID
		$pattern = '/201[\d]\/[\d\/]+[^\s]+[\d\s\.:]+ID:/s';

		$feedUrls = array();
		foreach ($sites as $site) {
			$feedUrls[] = $site['feed_url'];
		}
		// RSSフィードを並列に取得
		$feedOfSites = $this->RssUtil->getFeedParallel($feedUrls);
//debug($feedOfSites);
		// サイトの数ループ
		foreach ($feedOfSites as $i => $feedOfSite) {

			foreach ($feedOfSite as $entry) {
				// エントリ内のサマリーを検索

// desctiptionがない場合もある？
				if (preg_match($pattern, $entry['description'], $matches)) {
					// カテゴリIDを2chまとめの番号に
					$sites[$i]['category_id'] = Configure::read('Category.2chId');

					break;
				}
			}
		}
	}

}