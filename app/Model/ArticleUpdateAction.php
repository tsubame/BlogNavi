<?php

App::uses('AppModel', 'Model');
App::uses('ComponentCollection', 'Controller');
App::uses('CurlMultiComponent', 'Controller/Component');

/**
 * ツイート数を取得して記事を更新
 *
 *
 */
class ArticleUpdateAction extends AppModel {

	public $useTable = false;

	// Siteモデル
	private $Site;
	// Articleモデル
	private $Article;
	// TwitterAPIAccessorコンポーネント
	private $TwAccessor;

	/**
	 * Sitesモデルの配列
	 *
	 * @var unknown_type
	 */
	private $sites;

	/**
	 * 1回の処理でarticlesテーブルに保存する記事の件数
	 *
	 * @var int
	 */
	const SAVE_COUNT = 25;


	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		parent::__construct();

		$this->Site       = ClassRegistry::init('Site');
		$this->Article    = ClassRegistry::init('Article');
		$this->TwAccessor = ClassRegistry::init('TwitterAPIAccessor');
		$Collection       = new ComponentCollection();
	}

	/**
	 * 処理実行
	 *
	 */
	public function exec() {
		$articles = $this->Article->selectTodaysAllArticles();
		$urls = array();

		foreach ($articles as $article) {
			$urls[] = $article['url'];
		}

		// 記事のツイート数取得
		$tweetCounts = $this->TwAccessor->getTweetCountOfUrls($urls);

		$savedArticles = array();

		foreach ($tweetCounts as $url => $count) {

			foreach ($articles as $article) {
				if ($url == $article['url']) {
					$article['tweeted_count'] = $count;
					$savedArticles[] = $article;
				}
			}

		}

		$this->Article->saveAll($savedArticles);
	}



}