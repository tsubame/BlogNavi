<?php

App::uses('AppModel', 'Model');
App::uses('HttpSocket', 'Network/Http');
App::uses('ComponentCollection', 'Controller');
App::uses('CurlMultiComponent', 'Controller/Component');
App::uses('HttpUtilComponent', 'Controller/Component');

/**
 *
 * @author hid
 *
 */
class ArticleGetTweetCountAction extends AppModel {

	public $useTable = false;

	// Siteモデル
	private $Site;
	// Articleモデル
	private $Article;
	//
	//private $Category;
	//
	private $TwAccessor;

	//
	/**
	 * Sitesモデルの配列
	 *
	 * @var unknown_type
	 */
	private $sites;

	//コンポーネント
	//private $httpUtil;

	// 1回の処理でarticlesテーブルに保存する記事の件数
	const SAVE_COUNT = 25;

	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		parent::__construct();

		$this->Site       = ClassRegistry::init('Site');
		$this->Article    = ClassRegistry::init('Article');
		//$this->Category   = ClassRegistry::init('Category');
		$this->TwAccessor = ClassRegistry::init('TwitterAPIAccessor');
		$Collection       = new ComponentCollection();
		//$this->httpUtil   = new HttpUtilComponent($Collection);
	}

	/**
	 * 処理実行
	 */
	public function exec() {
		$articles = $this->Article->selectTodaysAllArticles();
//debug($articles);
		$urls = array();

		foreach ($articles as $article) {
			$urls[] = $article['url'];
		}

		// 記事のツイート数取得
		$tweetCounts = $this->TwAccessor->getTweetCountOfUrls($urls);
//debug($tweetCounts);

		$savedArticles = array();

		foreach ($tweetCounts as $url => $count) {

			foreach ($articles as $article) {
				if ($url == $article['url']) {
					$article['tweeted_count'] = $count;
					$savedArticles[] = $article;
				}
			}

		}
//debug($savedArticles);

		$this->Article->saveAll($savedArticles);
	}



}