<?php

App::uses('RssUtilComponent', 'Controller/Component');
App::uses('TwApiAccessorComponent', 'Controller/Component');

/**
 * ArticlesControllerのregisterFromArticleアクション
 *
 * スポーツナビ+ブログのRSSからサイトを登録する
 *
 * RSSの各記事のツイート数を取得して、
 * DBに保存済みのブログカテゴリの表示する記事よりも多ければサイトを登録
 *
 *
 * 依存クラス
 * ・Model/Site
 * ・Model/Article
 * ・Component/RssUtilComponent
 * ・ComponentCollection
 * ・Component/TwApiAccessorComponent
 *
 * エラー
 * ・サイトの情報がRSSで取得できない →
 */
class ArticleRegisterFromSNaviAction extends AppModel {

	/**
	 * テーブルの使用
	 *
	 * @var bool
	 */
	public $useTable = false;

	/**
	 * RssUtilコンポーネント
	 *
	 * @var object RssUtilComponent
	 */
	private $RssUtil;


	/**
	 * スポーツナビ+RSSのURL
	 *
	 * @var array
	 */
	private $sNaviPlusUrls = array(
				'http://www.plus-blog.sportsnavi.com/feed/centric/eusoccer/rss2_0.xml',
				'http://www.plus-blog.sportsnavi.com/feed/centric/soccer_wcup/rss2_0.xml',
				'http://www.plus-blog.sportsnavi.com/feed/centric/uefacl/rss2_0.xml',
				'http://www.plus-blog.sportsnavi.com/feed/centric/premierl/rss2_0.xml',
				'http://www.plus-blog.sportsnavi.com/feed/centric/seriea/rss2_0.xml',
				'http://www.plus-blog.sportsnavi.com/feed/centric/ligaesp/rss2_0.xml',
				'http://www.plus-blog.sportsnavi.com/feed/centric/bundesliga/rss2_0.xml',
				'http://www.plus-blog.sportsnavi.com/feed/centric/s_overseaslg/rss2_0.xml',
				'http://www.plus-blog.sportsnavi.com/feed/centric/soccer_overseaplayers/rss2_0.xml',
				'http://www.plus-blog.sportsnavi.com/feed/centric/soccer_japan/rss2_0.xml',
				'http://www.plus-blog.sportsnavi.com/feed/centric/jleague/rss2_0.xml',
				'http://www.plus-blog.sportsnavi.com/feed/centric/j1/rss2_0.xml',
				'http://www.plus-blog.sportsnavi.com/feed/centric/j2/rss2_0.xml',
				'http://www.plus-blog.sportsnavi.com/feed/centric/soccer/rss2_0.xml'
			);


	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		parent::__construct();

		$collection = new ComponentCollection();
		$this->RssUtil = new RssUtilComponent($collection);
	}

	/**
	 * 処理実行
	 *
	 */
	public function exec() {
		// スポーツナビプラスから24h以内の記事のURLを取得
		$articles = $this->getTodaysArticles();
		// URLの配列作成
		$urls = array();
		foreach($articles as $article) {
			$urls[] = $article['url'];
		}

		// 各記事のRT数取得
		$collection = new ComponentCollection();
		$twAccessor = new TwApiAccessorComponent($collection);
		$rtCounts   = $twAccessor->getTweetCountOfUrls($urls);

		// 記事を登録する基準のRT数を取得
		$minRtCount = $this->getSaveTweetedCount();

		$articleModel = ClassRegistry::init('Article');
		$siteModel = ClassRegistry::init('Site');

//debug($urls);
debug($rtCounts);

		// 記事を登録
		foreach ($articles as $article) {

			$url = $article['url'];
			$rtCount = $rtCounts[$url];

			if ($minRtCount < $rtCount) {

//debug($minRtCount);
				// サイトの情報をRSSで取得
				$site = $this->RssUtil->getSiteInfo($url);
				$site['registered_from'] = 'sports navi';
				$site['category_id']     = Configure::read('Category.blogId');
				$site['is_registered']   = false;

				// サイトのIDを取得
				$siteId = $siteModel->getIdFromUrl($site['url']);

				// サイトを登録
				if ($siteId == false) {
					$siteId = $siteModel->saveIfNotExists($site);
				}

				$article['tweeted_count'] = $rtCount;
				$article['site_id'] = $siteId;
				$articleModel->saveIfNotExists($article);

				debug("{$rtCount} : {$site['name']} : \n {$article['url']}");
			}
		}
	}

	/**
	 * スポーツナビ+のRSSから24時間以内の記事のURLを取得
	 *
	 * @return array $urls 記事のURLの配列
	 */
	protected function getTodaysArticleUrls() {
		$articles = $this->getTodaysArticles();

		$urls = array();
		foreach($articles as $article) {
			$urls[] = $article['url'];
		}

		return $urls;
	}

	/**
	 * スポーツナビ+のRSSから24時間以内の記事を取得
	 *
	 * @return array $articles 記事の配列
	 */
	protected function getTodaysArticles() {
		// 24時間前のタイムスタンプ取得
		$oneDayAgoTs = strtotime('-1 day');

		// RSSを並列に取得
		$feedRow = $this->RssUtil->getFeedParallel($this->sNaviPlusUrls);

		// 記事を取り出す
		$articles = array();
		foreach ($feedRow as $i => $feed) {
			foreach ($feed as $article) {
				// 重複記事はスキップ
				$isSaved = false;
				foreach ($articles as $savedArticle) {
					if ($savedArticle['url'] == $article['url']) {
						$isSaved = true;
					}
				}

				if ($isSaved == true) {
					continue;
				}

				$articleTs = strtotime($article['published']);

				// 日付を比較 24時間以内の記事なら記事を配列に保存
				if ($oneDayAgoTs < $articleTs) {
					$articles[] = $article;
				}
			}
		}

		return $articles;
	}

	/**
	 * 記事を登録する基準となるRT数を取得
	 * 24h以内のブログカテゴリの人気記事10件以内に入るRT数
	 *
	 * @return int $minRTCount
	 */
	protected function getSaveTweetedCount() {
		// ブログカテゴリの人気記事を取得
		$articleModel = ClassRegistry::init('Article');
		$popArticles  = $articleModel->selectTodaysBlogArticles();
		// 配列の最後の要素のツイート数を取得
		$arrayCount = count($popArticles);

		if ($arrayCount <= 0) {
			return 0;
		}

		$minRTCount = $popArticles[$arrayCount - 1]['Article']['tweeted_count'];
// 最小のRT
	if ($minRTCount < 2) {
		$minRTCount = 2;
	}

		return $minRTCount;
	}

}