<?php

/**
 * 記事登録処理
 *
 * ArticleコントローラのInsertアクションのロジック
 *
 *
 *
 *（依存クラス）
 * ・
 *
 *
 *（概要）
 *
 * DBに保存したニュースサイトのURLでtwitterを検索して、そのサイト内の記事を取得する。
 * 取得した記事のうちツイート数の多いものをarticlesテーブルに保存。 *
 *
 *
 *（処理手順）
 *
 * ・sitesテーブルからカテゴリ別にニュースサイトをすべて取得
 *
 * ・twitter検索APIで各サイトのアドレスを検索し、結果をJSON形式で取得
 *     サイトのURLは検索用にフォーマットして検索する（httpやwwwが含まれるとうまく検索できない）
 *     htttp://www.yahoo.co.jp/ → yahoo.co.jp/
 *
 * ・JSONデータから検索結果内のURL（t.co形式の短縮URL）を取り出す
 *
 * ・短縮URLをまとめて展開
 *
 * ・展開後のURLから重複分を除いて配列に保存
 *   ニュースサイトに関係ないアドレスのデータも除く
 *
 * ・各URLのツイート数をtwitterAPIで取得
 *
 * ・ツイート数が多いURLを25件取得
 *
 * ・それぞれのURLのタイトルを取得
 *
 * ・URL、タイトル、サイトID、ツイート数を記事の配列に保存
 *
 * ・articlesテーブルにデータを保存
 *   すでに保存されているデータは保存しない
 *
 */
App::uses('AppModel', 'Model');
App::uses('HttpSocket', 'Network/Http');
App::uses('ComponentCollection', 'Controller');
App::uses('CurlMultiComponent', 'Controller/Component');
App::uses('HttpUtilComponent', 'Controller/Component');

class ArticleInsertByTwAction extends AppModel {

	public $useTable = false;

	// Siteモデル
	private $Site;
	// Articleモデル
	private $Article;
	//
	private $Category;
	//
	private $TwAccessor;

	// Sitesモデルの配列
	private $sites;

	//コンポーネント
	private $httpUtil;

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
		$this->Category   = ClassRegistry::init('Category');
		$this->TwAccessor = ClassRegistry::init('TwitterAPIAccessor');
		$Collection       = new ComponentCollection();
		$this->httpUtil   = new HttpUtilComponent($Collection);
	}

	/**
	 * 処理実行
	 */
	public function exec() {

		// カテゴリの件数ループ
		$categories = $this->Category->getAllCategories();
		// すべてのサイトを取得（不要？）
		$this->sites = $this->Site->getAllSites();

		foreach ($categories as $category) {
			$sites = $this->Site->getSitesOfCategory($category['id']);
			// カテゴリ別に検索する必要あり
			// ツイッターを検索
			$tweetedUrls = $this->searchUrls($sites);

			// 記事のツイート数取得
			$tweetCounts = $this->TwAccessor->getTweetCountOfUrls($tweetedUrls);
			// 上位25件の記事を取得
			$articles = $this->pickUpInsertArticles($tweetCounts);

			// ボトルネック
			// タイトル取得
			$articles = $this->getArticlesTitle($articles);

			debug($articles);
			// DBに存在しないデータを追加
			$this->saveNotExistArticles($articles);
		}
	}

	/**
	 * RSSで記事を取得
	 *
	 */
	public function insertArticlesbyRss() {

	// 6時間ごとに実行　

		// カテゴリごとにDBからサイトを取得

		// 各サイトのRSSを取得

		// DBに記事を登録


	// 24時間ごとに実行

		// ツイート数の少ない木を削除

	}


	/**
	 * 検索処理
	 * URLの配列を返す
	 *
	 * @param array  $sites
	 * @return array $tweetedUrls URLの配列
	 */
	protected function searchUrls($sites) {

		// ツイッターを検索してURLの配列を取得
		$urls = $this->getUrlsBySearchTwitter($sites);
		// 短縮URLを展開
		//$longUrls = $this->curlMulti->expandUrls($shortUrls);

		// 重複分を除く
		$tweetedUrls = array();
		foreach ($urls as $url) {
			// すでに取得したURLと重複している場合はスキップ
			if (array_search($url, $tweetedUrls)) {
				continue;
			}

			array_push($tweetedUrls, $url);
		}

		return $tweetedUrls;
	}

	/**
	 * サイトのURLでツイッターを検索して検索結果からURLを抜き出し、配列形式でを返す
	 *
	 * 検索は並列に行う
	 *
	 * @param  array $sites
	 * @return array $tweetedUrls
	 */
	protected function getUrlsBySearchTwitter($sites) {

		// URLの配列を作成
		$reqUrls = array();
		foreach ($sites as $site) {
			$reqUrl = $site['url'];
			array_push($reqUrls, $reqUrl);
		}
		$tweetedUrls = $this->TwAccessor->getTweetedUrlsBySearchUrl($reqUrls);

		return $tweetedUrls;
	}

// 要テスト
	/**
	 * 記事のURLを見てサイトのIDを取得する
	 *
	 * 記事のURLにサイトのURLが含まれればそのサイトのIDを返す
	 * サイトのURLと同一の場合はfalse
	 * 最後にindex.htmlが含まれていてもfalse
	 *
	 * @param  string $entryUrl
	 * @return mixed  int サイトID false 含まれない
	 */
	protected function getSiteIdFromUrl($url) {
		// サイトの件数ループ
		foreach ($this->sites as $site) {

			$siteUrl = $site['url'];
			// 正規表現比較用にエスケープ "/ . - ?"の4つに\を付ける
			$escSiteUrl = addcslashes($siteUrl, '/.-?');

			// サイトのURLと同じ、もしくは"サイトのURL/index.～"の場合はfalse
			if ($url == $siteUrl || $url == "{$siteUrl}/") {
				return false;
			} else if (preg_match('/' . $escSiteUrl . '.*index[\w\.\-_]+/', $url)) {
				return false;
			}

			// 記事のURLにサイトのURLが含まれるか比較
			$result = strpos(strtoupper($url), strtoupper($siteUrl));
			if ($result !== false) {
				return $site['id'];
			}
		}

		return false;
	}


// 要テスト
	/**
	 * ツイート数の配列から上位25件のみを取り出して配列に保存
	 *
	 * @param array $tweetCounts array('記事のURL' => ツイート数)
	 */
	protected function pickUpInsertArticles($tweetCounts) {
		// 降順に並び替え
		$res = arsort($tweetCounts, SORT_NUMERIC);
		debug($tweetCounts);

		$articles = array();
		foreach ($tweetCounts as $url => $count) {
			if (self::SAVE_COUNT <= count($articles)) {
				break;
			}
			// URLを見てサイトIDを取得
			$siteId = $this->getSiteIdFromUrl($url);
			if ($siteId == false) {
				continue;
			}

			$article = array(
					'url' => $url,
					'tweeted_count' => $count,
					'site_id' => $siteId
			);
			array_push($articles, $article);
		}

		return $articles;
	}

	// 出来れば並列化
	/**
	 * DBに追加する記事のタイトルを取得
	 *
	 * @param  array $articles
	 * @return       $articles
	 */
	protected function getArticlesTitle($articles) {
		// 記事のタイトルを取得
		foreach ($articles as $i => $article) {
			$title = $this->httpUtil->getSiteName($article['url']);

			$articles[$i]['title'] = $title;
		}

		return $articles;
	}

	/**
	 * DBに存在しないデータを追加
	 *
	 */
	protected function saveNotExistArticles($articles) {

		foreach ($articles as $article) {
			// 同じURLのデータが存在するか調べる
			$result = $this->Article->hasAny(
					array('url' => $article['url'])
			);

			// なければ追加
			if ($result == false) {
				$this->Article->create();
				$this->Article->save($article);
				debug('追加しました' . $article['url']);
			}
		}
	}





}