<?php

/**

 *
 *
 *
 */
App::uses('AppModel', 'Model');
App::uses('ComponentCollection', 'Controller');
App::uses('HttpUtilComponent', 'Controller/Component');
App::uses('RssFetcherComponent', 'Controller/Component');




/***
 *
 *  ブログ自動登録の仕様
 *
 *  ・定期的に実行
 *  ・登録済みのものは登録しない
 *  ・削除ボタンで削除 deleted を trueに
 *	・カテゴリ未分類の列を付ける
 *
 */

/**
 * SitesControllerのregisterAutoアクション
 *
 */
class SiteRegisterAutoAction extends AppModel {


	/**
	 * テーブルの使用
	 *
	 * @var bool
	 */
	public $useTable = false;

	/**
	 * Siteモデル
	 *
	 * @var object Site
	 */
	private $Site;

	/**
	 *
	 * @var object HttpUtilComponent
	 */
	private $HttpUtil;

	/**
	 *
	 * @var object RssFetcherComponent
	 */
	private $RssFetcher;

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
	 * 2chまとめのカテゴリーID
	 *
	 * @var int
	 */
	const CATEGORY_2CH_ID = 2;

	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		parent::__construct();

		$collection = new ComponentCollection();
		$this->Site       = ClassRegistry::init('Site');
		$this->HttpUtil   = new HttpUtilComponent($collection);
		$this->RssFetcher = new RssFetcherComponent($collection);
	}

	/**
	 * 処理実行
	 *
	 */
	public function exec() {

		// FC2、ライブドアのランキングサイトを取得
		$fc2Sites = $this->getFc2RankSites();
		$ldSites  = $this->getLivedoorRankSites();

		$sites = array_merge($fc2Sites, $ldSites);

		// フィードURL、その他の値を設定
		foreach ($sites as $i => $site) {
			// フィードURLを取得
			$sites[$i]['feed_url'] = $this->RssFetcher->getFeedUrlFromSiteUrl($site['url']);

			if ($sites[$i]['feed_url'] == false) {
				debug("フィードURLを取得できませんでした  \n{$site['url']}");
			}

			$sites[$i]['is_registered']  = false;
			$sites[$i]['category_id']    = 3;
		}

		// カテゴライズ
		$this->autoCategorize($sites);

		debug($sites);

		// サイトを登録
		foreach ($sites as $site) {
			$this->Site->saveIfNotExists($site);
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
	 */
	protected function getFc2RankSites() {

		$tagPattern    = '/<a[^>]+[\w]+\.blog[\d]*\.fc2\.com\/[^>]+title[^>]+>/is';
		$namePattern   = '/title[^"]+"([^"]+)"/is';
		$urlPattern    = '/http:\/\/[\w]+\.blog[\d]*\.fc2\.com\//';
		$registerCount = 30;

		$html = $this->HttpUtil->getContents(self::FC2_RANK_URL);
		$sites = array();

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
	 */
	protected function getLivedoorRankSites() {

		$tagPattern    = '/<h3[\s]+class\="ttl">.+?<\/h3>/is';
		$urlPattern    = '/http:\/\/[\w\/\.\-_]+/is';
		$namePattern   = '/位">([^<]+)<\/a/is';
		$registerCount = 50;

		// HTMLを取得
		$html = $this->HttpUtil->getContents(self::LIVEDOOR_RANK_URL);
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
	 * 2chまとめブログをカテゴライズ
	 *
	 */
	public function autoCategorize(&$sites) {

		$pattern = '/201[\d]\/[\d\/]+[^\s]+[\d\s\.:]+ID:/s';

		// 未登録サイトを取得
		//$sites = $this->Site->getUnRegiSites();

		$feedUrls = array();
		foreach ($sites as $site) {
			$feedUrls[] = $site['feed_url'];
		}
		// RSSフィードを並列に取得
		$feedOfSites = $this->RssFetcher->getFeedParallel($feedUrls);

		// サイトの数ループ
		foreach ($feedOfSites as $i => $feedOfSite) {
			foreach ($feedOfSite as $entry) {
				// エントリ内のサマリーを検索
				if (preg_match($pattern, $entry['description'], $matches)) {
					$sites[$i]['category_id'] = self::CATEGORY_2CH_ID;

					break;
				}
			}
		}
	}

}