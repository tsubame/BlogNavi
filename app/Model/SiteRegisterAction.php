<?php
App::uses('ComponentCollection', 'Controller');
App::uses('RssUtilComponent', 'Controller/Component');

/**
 * SitesControllerのregisterアクション
 *
 * 登録フォームからサイトを登録する
 * 登録フォームの必須項目はカテゴリとURLだけなので、フィードURL、サイト名が入力されていない場合はRSSで取得する
 *
 *
 * 依存クラス
 *
 * ・Component/RssUtilComponent
 * ・ComponentCollection
 * ・Model/Site
 *
 * エラー
 *
 * ・カテゴリIDが無い → viewでチェック
 * ・URLが無い → viewでチェック
 *
 * ・サイト名、フィードURLが取得できない → エラーメッセージを表示 ログに書き込む コントローラ側で対応
 * ・フォーム以外から直接アクセスされた  → エラーメッセージを表示 ログに書き込む コントローラ側で対応
 */
class SiteRegisterAction extends AppModel {

	/**
	 * テーブルの使用
	 *
	 * @var bool
	 */
	public $useTable = false;

	/**
	 * エラーメッセージ
	 *
	 * @var string
	 */
	private $errorMessage = null;


	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		parent::__construct();

		$this->Site = ClassRegistry::init('Site');
	}

	/**
	 * 処理実行
	 *
	 * @param array $site サイト
	 * 					$site => array('url' => URL, 'category_id' => カテゴリID, 'name' => 名前, 'feed_url' => フィードURL)
	 */
	public function exec($site) {
		// サイトの情報取得
		$site = $this->getSiteInfoFromRss($site);

		// 登録
		$siteModel = ClassRegistry::init('Site');
		$siteModel->saveIfNotExists($site);
	}

	/**
	 * RSSでサイトの情報取得
	 */
	protected function getSiteInfoFromRss($site) {
		$collection = new ComponentCollection();
		$rssUtil    = new RssUtilComponent($collection);

		// RSS経由でサイトの情報取得
		$siteInfo = $rssUtil->getSiteInfo($site['url']);

		// フィードURLを設定
		if ( !isset($site['feed_url']) || $site['feed_url'] == '') {
			$feedUrl = $siteInfo['feed_url'];

			if ($feedUrl != false) {
				$site['feed_url'] = $feedUrl;
			} else {
				$this->errorMessage = 'フィードURLを取得できませんでした。';
			}
		}

		// サイト名を設定
		if ( !isset($site['name']) || $site['name'] == '') {
			$siteName = $siteInfo['name'];

			if ($siteName != false) {
				$site['name'] = $siteName;
			} else {
				$this->errorMessage = 'サイト名を取得できませんでした。';
			}
		}

		return $site;
	}


	/**
	 * エラーメッセージを返す
	 *
	 * @return string
	 */
	public function getErrorMessage() {
		return $this->errorMessage;
	}

}