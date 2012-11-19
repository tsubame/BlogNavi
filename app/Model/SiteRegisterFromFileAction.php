<?php

App::uses('AppModel', 'Model');
App::uses('ComponentCollection', 'Controller');
App::uses('HttpUtilComponent', 'Controller/Component');
App::uses('RssFetcherComponent', 'Controller/Component');

/**
 * SitesControllerのregisterFromFileアクション
 *
 * ファイルからサイトを登録する
 *
 *
 * 仕様
 *
 * ・webroot/file/以下のファイルを使用
 * ・1行に1つサイトのURLを書く
 * ・URLの後にスペースでサイト名を書くことができる。ない場合はRSSからサイト名を自動取得
 *
 * ファイルの書き方の例
 *
 *   http://yahoo.co.jp/ ヤフー
 *   http://google.co.jp/ google
 *
 * 必要なクラス
 * ・RssFetcherComponent
 * ・Sitesモデル
 *
 * エラー処理
 *
 * ・ファイルが開けないとき → ログにエラーメッセージを記録して処理続行
 * ・書式がおかしい行 → ログに記録して処理続行
 * ・フィードURLが取得できないとき → ログに記録してスキップ
 *
 */
class SiteRegisterFromFileAction extends AppModel {


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
	 * RssFetcherコンポーネント
	 *
	 * @var object RssFetcherComponent
	 */
	private $RssFetcher;

	/**
	 * ニュースサイトのファイル名
	 *
	 * @var string
	 */
	const FILE_NEWS = 'sites_news.txt';

	/**
	 * 2chまとめサイトのファイル名
	 *
	 * @var string
	 */
	const FILE_2CH = 'sites_2ch.txt';

	/**
	 * ブログサイトのファイル名
	 *
	 * @var string
	 */
	const FILE_BLOG = 'sites_blog.txt';

	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		parent::__construct();

		$collection = new ComponentCollection();
		$this->Site       = ClassRegistry::init('Site');
		//$this->HttpUtil   = new HttpUtilComponent($collection);
		$this->RssFetcher = new RssFetcherComponent($collection);
	}

	/**
	 * 処理実行
	 *
	 */
	public function exec() {
		$this->register(1, self::FILE_NEWS);
		$this->register(2, self::FILE_2CH);
		$this->register(3, self::FILE_BLOG);
	}

	/**
	 * 登録処理
	 *
	 * @param  int $catId カテゴリ番号
	 * @return bool false or true
	 */
	protected function register($catId = 1, $fileName) {

		// ファイル内のテキストを取得
		try {
			$file = WWW_ROOT . 'files' . DS . $fileName;
			$text = file_get_contents($file);
		} catch(Exception $e) {
			// ロギング ファイルが開けません
			CakeLog::warning("ファイルが開けません " . $e->getMessage());
			return false;
		}

		// テキストから配列にURL、サイト名を取得
		$sites = $this->splitUrlAndSiteName($text);

		// サイトのフィードURL、サイト名をRSSで取得する
		foreach ($sites as $site) {
			$site = $this->getFeedUrlAndSiteName($site);

			$site['category_id'] = $catId;
			$site['registered_from'] = 'file';

			// DBに保存
			$this->Site->saveIfNotExists($site);
		}

		return true;
	}

	/**
	 * サイトのフィードURL、サイト名をRSS経由で取得する
	 *
	 * サイト名はnullでない時のみ（ファイルに書いていないとき）取得する
	 *
	 * @param array $site
	 */
	protected function getFeedUrlAndSiteName($site) {
		// フィードURLを取得
		$feedUrl = $this->RssFetcher->getFeedUrlFromSiteUrl($site['url']);

		if ($feedUrl != false) {
			$site['feed_url'] = $feedUrl;
		} else {
			// ロギング
			CakeLog::warning("フィードURLを取得できませんでした {$url}");
			continue;
		}

		// サイト名を取得
		if (is_null($site['name'])) {
			$siteName = $this->RssFetcher->getSiteName($feedUrl);

			if ($siteName != false) {
				$site['name'] = $siteName;
			} else {
				CakeLog::warning("サイト名を取得できませんでした {$url}");
			}
		}

		return $site;
	}

	/**
	 * 複数行のテキストからURLとサイト名を取り出す
	 * サイト名がないときはnullをセット
	 *
	 * @param  string $text
	 * @return array  $sites
	 * 					$sites => array(
	 * 						[0] => array(
	 * 							['url']  => URL,
	 * 							['name'] => サイト名
	 * 						)
	 * 					)
	 */
	protected function splitUrlAndSiteName($text) {
		$urlPattern = '/^http:\/\/[\w\.\-\/_=?&@:]+/';
		$sites = array();

		// 配列にURL、名前を取得
		$lines = explode("\n", $text);
		// 行数分ループ
		foreach ($lines as $i => $line) {
			$line = trim($line);

			$site = array();
			// URLを抽出
			if ( !preg_match($urlPattern, $line, $matches)) {
				// ロギング 書式がおかしいです URLがありません
				CakeLog::warning("ファイルの行にURLがありません " . $line);
				continue;
			}
			$site['url'] = $matches[0];

			// サイト名を抽出
			$siteName = str_replace($site['url'], '', $line);
			if ($siteName != '') {
				$site['name'] = trim($siteName);
			} else {
				$site['name'] = null;
			}

			$sites[] = $site;
		}

		return $sites;
	}


}