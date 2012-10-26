<?php

/**
 * SitesControllerのregisterアクション
 *
 */
class SiteRegisterAction extends AppModel {


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
	 * コンポーネントのインスタンス生成用引数
	 *
	 * @var object ComponentCollection
	 */
	private $collection;

	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		parent::__construct();

		$this->Site       = ClassRegistry::init('Site');
		$this->collection = new ComponentCollection();
	}

	/**
	 * 処理実行
	 *
	 */
	public function exec($site) {

		$rssFetcher = new RssFetcherComponent($this->collection);

		// ファイル名を取り除く
		if (preg_match('/^(http:\/\/[\w\.\/\-_=]+\/)[\w\-\._=]*$/', $site['url'], $matches)) {
			$site['url'] = $matches[1];
		}

		// フィードURLを取得
		$feedUrl = $rssFetcher->getFeedUrlFromSiteUrl($site['url']);
		if ($feedUrl != false) {
			$site['feed_url'] = $feedUrl;
		} else {
			debug('フィードURLを取得できませんでした');
		}

		// サイト名を取得
		$siteName = $rssFetcher->getSiteName($feedUrl);
		if ($siteName != false) {
			$site['name'] = $siteName;
		} else {
			debug('サイト名を取得できませんでした');
		}

		// 登録
		$this->Site->saveIfNotExists($site);
	}


}