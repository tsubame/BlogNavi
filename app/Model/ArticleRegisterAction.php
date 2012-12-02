<?php
App::uses('AppModel', 'Model');
App::uses('ComponentCollection', 'Controller');
App::uses('RssUtilComponent', 'Controller/Component');

/**
 * 記事登録処理
 *
 * ArticleコントローラのRegisterアクションのロジック
 *
 *
 *
 */
class ArticleRegisterAction extends AppModel {

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
	 * Articleモデル
	 *
	 * @var object Article
	 */
	private $Article;

	/**
	 * コンポーネントのインスタンス生成用引数
	 *
	 * @var object ComponentCollection
	 */
	private $Collection;

	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		parent::__construct();

		$this->Site       = ClassRegistry::init('Site');
		$this->Article    = ClassRegistry::init('Article');
		$this->Collection = new ComponentCollection();
	}

	/**
	 * 処理実行
	 *
	 */
	public function exec() {
		// sitesテーブルからサイトを取得
		$sites = $this->Site->getSites();

		$feedUrls = array();
		// URLを配列に入れる
		foreach ($sites as $i => $site) {
			// フィードURLを配列に入れる なければサイトのURL
			if (isset($site['feed_url'])) {
				array_push($feedUrls, $site['feed_url']);
			} else {
				array_push($feedUrls, $site['url']);
			}
		}
		// 並列にRSSフィードを取得
		$fetcher = new RssUtilComponent($this->Collection);
		$feedRow = $fetcher->getFeedParallel($feedUrls);

		foreach ($feedRow as $i => $feed) {
			foreach ($feed as $article) {
				$article['site_id'] = $sites[$i]['id'];

				$this->Article->saveIfNotExists($article);
			}
		}
	}

}