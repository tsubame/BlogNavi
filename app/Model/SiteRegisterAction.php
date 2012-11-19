<?php
App::uses('ComponentCollection', 'Controller');
App::uses('RssFetcherComponent', 'Controller/Component');

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
	 *
	 * @var object
	 */
	private $rssFetcher;


	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		parent::__construct();

		$collection = new ComponentCollection();
		$this->Site = ClassRegistry::init('Site');
		$this->rssFetcher = new RssFetcherComponent($collection);
	}

	/**
	 * 処理実行
	 *
	 */
	public function exec($site) {
		// フィードURLを取得
		if ( !isset($site['feed_url']) || $site['feed_url'] == '') {
			$feedUrl = $this->rssFetcher->getFeedUrlFromSiteUrl($site['url']);
			if ($feedUrl != false) {
				$site['feed_url'] = $feedUrl;
			} else {
				debug('フィードURLを取得できませんでした');
			}
		}

		// サイト名を取得
		if ( !isset($site['name']) || $site['name'] == '') {
			$siteName = $this->rssFetcher->getSiteName($feedUrl);
			if ($siteName != false) {
				$site['name'] = $siteName;
			} else {
				debug('サイト名を取得できませんでした');
			}
		}

		// 登録
		$this->Site->saveIfNotExists($site);
	}



}