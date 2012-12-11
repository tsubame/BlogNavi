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

		$saveCount = 0;

		foreach ($feedRow as $i => $feed) {
			foreach ($feed as $article) {
				$article['site_id'] = $sites[$i]['id'];

				$result = $this->saveArticle($article);

				if ($result !== false) {
					$saveCount++;
				}
			}
		}
		// ロギング
		CakeLog::info("{$saveCount}件の記事を登録しました。");
	}

	/**
	 * 登録処理
	 * 記事の発行日時が○時間以上前の記事は登録しない
	 *
	 * 何時間前の記事から登録するかは定数にて決める
	 *
	 * @see    const->Article.registerPastHourFrom
	 * @param  array $article
	 * @return bool
	 */
	protected function saveArticle($article) {
		$pubTs = strtotime($article['published']);
		$intervalSec = Configure::read('Article.registerPastHourFrom') * 3600;
		$nowTs = time();

		if (($nowTs - $pubTs) < $intervalSec) {
			$result = $this->Article->saveIfNotExists($article);

			return true;
		} else {
			return false;
		}
	}

}