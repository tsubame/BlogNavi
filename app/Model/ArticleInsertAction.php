<?php

/**
 * 記事登録処理
 *
 * ArticleコントローラのInsertアクションのロジック
 *
 * (メモ)
 *
 * ・出来れば並列化できるところはしたい
 *   ・検索処理
 *   ・タイトルの取得
 *
 *
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

class ArticleInsertAction extends AppModel {

	public $useTable = false;

	// Siteモデル
	private $Site;
	// Articleモデル
	private $Article;
	// Sitesモデルの配列
	private $sites;

	//コンポーネント
	private $httpUtil;
	private $curlMulti;

	// 検索した記事の配列
	private $searchedArticles = array();
	// テーブルに保存する記事の配列
	private $insertArticles = array();

	// twitter検索APIのURL
	const SEARCH_API_URL = "http://search.twitter.com/search.json";
	// twitterツイート数取得APIのURL
	const COUNT_API_URL = 'http://urls.api.twitter.com/1/urls/count.json?url=';
	// 検索する件数
	const RPP = 30;
	// 一度にDBに保存する記事の件数
	const SAVE_COUNT = 25;

	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		parent::__construct();

		$this->Site = ClassRegistry::init('Site');
		$this->Article = ClassRegistry::init('Article');
		$Collection = new ComponentCollection();
		$this->curlMulti = new CurlMultiComponent($Collection);
		$this->httpUtil  = new HttpUtilComponent($Collection);
	}

	/**
	 * 処理実行
	 */
	public function exec() {
		$this->sites = $this->Site->getAllSites();
		// ツイッター検索
		$this->searchSingleThread();
		// ツイート数取得
		$this->getTweetCountOfArticles();
		// タイトル取得
		$this->getArticlesTitle();
		// DBに存在しないデータを追加
		$this->saveNotExistArticles();
	}

	/**
	 * 検索処理
	 *
	 * 並列処理を行わないロジック
	 *
	 */
	private function searchSingleThread() {
		$articles = array();
		// サイトの件数ループ
		foreach ($this->sites as $site) {
			// ツイッターを検索して記事を取得
			$articles = array_merge($articles, $this->searchTwitterBySiteUrl($site));
		}
		// URLのみ取り出す
		$shortUrls = array();
		foreach ($articles as $data) {
			array_push($shortUrls, $data['url']);
		}

		// 短縮URLを展開して配列に入れなおす
		$longUrls = $this->curlMulti->expandUrls($shortUrls);
		for ($i = 0; $i < count($articles); $i++) {
			$articles[$i]['url'] = $longUrls[$i];
		}

		// 重複分を除いて配列に記事を保存
		$uniqueUrls  = array();
		foreach ($articles as $article) {
			// すでに取得したURLと重複している場合はスキップ
			if (array_search($article['url'], $uniqueUrls)) {
				continue;
			}
			// URLを見て登録済みのサイトの記事のアドレスでなければスキップ
			$result = $this->isValidArticleUrl($article['url']);
			if ($result == false) {
				continue;
			}

			array_push($uniqueUrls, $article['url']);
			array_push($this->searchedArticles, $article);
		}
	}

	/**
	 * サイトのURLでツイッターを検索してサイトの記事を取得
	 *
	 * 検索結果のツイート内のURLを取得し、サイトのIDとペアにして配列を返す
	 *
	 * @param  array $site
	 * @return array $articles
	 *
	 *・戻り値の配列
	 *  Array( 0 =>
	 *  		Array('url' => ツイート内のURL,
	 *  			  'site_id' => サイトID)
	 *  	)
	 */
	private function searchTwitterBySiteUrl($site) {
		$articles   = array();
		$uniqueUrls = array();

		// twitterAPIでサイトのURLが含まれるツイートを検索
		$tweets = $this->searchTwitter($site['url']);
		// ツイートの件数ループ
		foreach ($tweets as $tweet) {
			// ツイート内のURLの要素を取得
			$urlRow = end($tweet["entities"]["urls"]);// 2件以上のURLが含まれる場合は最後のURLを取得
			if ( !isset($urlRow["expanded_url"]) ) {
				continue;
			}
			// t.co形式のURLを取得
			$url = $urlRow["expanded_url"];

			// すでに取得したURLと重複している場合はスキップ
			if (array_search($url, $uniqueUrls)) {
				continue;
			}
			// URLとサイトのIDを配列に保存
			$article['url']     = $url;
			$article['site_id'] = $site['id'];
			array_push($articles, $article);

			array_push($uniqueUrls, $url);
		}

		return $articles;
	}

	/**
	 * ツイッターを検索して結果をJSON形式で取得
	 *
	 * 検索前にURLを検索用の文字列にフォーマットする
	 * 取得したJSONデータはデコードして配列で返す
	 *
	 * ・戻り値の配列の形式
	 *
	 * $tweets[番号]['text'] => ツイート本文
	 *				['entities']['urls'][番号]['expanded_url'] => 展開後のURL
	 *
	 *
	 * @param  string $url 	  URL
	 * @return array  $tweets 配列
	 */
	private function searchTwitter($url) {
		$socket = new HttpSocket();

		// URLを検索用キーワードの形式にフォーマット
		$searchKey = $this->formatUrlForSearch($url);
		// 1日前の日付を取得
		$today = date('Y-m-d', strtotime('-1 day'));
		// パラメータ設定 今日の日付ツイートを取得 q=○○+since:2012-09-10
		$param = "rpp=" . self::RPP . "&q={$searchKey}+since:{$today}&include_entities=true";

		// GETリクエスト
		$res   = $socket->get(self::SEARCH_API_URL, $param);
		// HTTPのBody取得
		$json  = $res["body"];
		// JSONデータを配列にデコード
		$array = json_decode($json, true);
		$tweets = $array["results"];

		return $tweets;
	}


	/**
	 * URLを検索キーワードの形式にフォーマット
	 *
	 * http://と最後のファイル名、www.、?以降を取り除く
	 * （例）http://www.uefa.com/index.html → uefa.com/
	 *
	 * @param  string $url
	 * @return string $searchKey
	 */
	private function formatUrlForSearch($url) {
		$searchKey = $url;

		// ?以降を除く
		if (preg_match('/^http:\/\/[\w\.\/\-_=]+/', $searchKey, $matches)) {
			$searchKey = $matches[0];
		}
		// http://とファイル名を取り除く
		if (preg_match('/^http:\/\/([\w\.\/\-_=]+\/)[\w\-\._=]*$/', $searchKey, $matches)) {
			$searchKey = $matches[1];
			// www.を取り除く
			if (substr($searchKey, 0, 4) == 'www.') {
				$searchKey = substr($searchKey, 4);
			}
		}
		//debug('検索キーワード' . $searchKey);

		return $searchKey;
	}

	/**
	 * 記事のURLを見て登録済のサイトの記事であるかを調べる
	 *
	 * 記事のURLにサイトのURLが含まれればtrue
	 * サイトのURLと同一の場合はfalse
	 *
	 * @param  string $entryUrl
	 * @return bool   true => 含まれる
	 */
	private function isValidArticleUrl($url) {
		// サイトの件数ループ
		foreach ($this->sites as $site) {

			$siteUrl = $site['url'];
			// 正規表現比較用にエスケープ "/ . - ?"の4つに\を付ける
			$escSiteUrl = addcslashes($siteUrl, '/.-?');

			// サイトのURLと同じ、もしくは"サイトのURL/index.～"の場合はfalse
			if ($url == $siteUrl || $url == "{$siteUrl}/") {
				return false;
			} else if (preg_match('/' . $escSiteUrl . '\/?index\.[\w]+/', $url)) {
				return false;
			}

			// 記事のURLにサイトのURLが含まれるか比較
			$result = strpos(strtoupper($url), strtoupper($siteUrl));
			if ($result !== false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * 検索した記事のツイート数を取得
	 *
	 */
	private function getTweetCountOfArticles() {
		$reqUrls = array();
		// APIアクセス用のURLの配列を作成
		foreach ($this->searchedArticles as $article) {
			$reqUrl = self::COUNT_API_URL . $article['url'];;
			array_push($reqUrls, $reqUrl);
		}
		// 並列にAPIにアクセス
		$jsons = $this->curlMulti->getContents($reqUrls);

		// ツイート数を配列に取得 array('記事のURL' => ツイート数)
		$tweetCounts = array();
		foreach ($jsons as $i => $json) {
			$data = json_decode($json, true);
			$this->searchedArticles[$i]['tweeted_count'] = $data['count'];
			// 最後の / を外す
			$url = $data['url'];
			$url = substr($url, 0, strlen($url) - 1);

			$tweetCounts[$url] = $data['count'];
		}

		// 上位25件を取得
		$this->pickUpInsertArticles($tweetCounts);
	}

	/**
	 * ツイート数の配列から上位25件のみを取り出して配列に保存
	 *
	 * @param array $tweetCounts array('記事のURL' => ツイート数)
	 */
	private function pickUpInsertArticles($tweetCounts) {
		// 降順に並び替え
		$res = arsort($tweetCounts, SORT_NUMERIC);

		foreach ($tweetCounts as $url => $count) {
			if (self::SAVE_COUNT <= count($this->insertArticles)) {
				break;
			}

			$pickUps = false;
			// サイトIDを取得する
			foreach ($this->searchedArticles as $article) {
				if ($article['url'] == $url) {
					$siteId = $article['site_id'];
					$pickUps = true;
					break;
				}
			}
			if ($pickUps == false) {
				continue;
			}

			$article = array(
					'url' => $url,
					'tweeted_count' => $count,
					'site_id' => $siteId
			);
			array_push($this->insertArticles, $article);
		}
	}

	// 出来れば並列化
	/**
	 * DBに追加する記事のタイトルを取得
	 *
	 *
	 */
	private function getArticlesTitle() {
		// 記事のタイトルを取得
		foreach ($this->insertArticles as $i => $article) {
			$title = $this->httpUtil->getSiteName($article['url']);

			$this->insertArticles[$i]['title'] = $title;
		}
	}

	/**
	 * DBに存在しないデータを追加
	 *
	 */
	private function saveNotExistArticles() {
		debug($this->insertArticles);

		foreach ($this->insertArticles as $article) {
			// 同じURLのデータが存在するか調べる
			$result = $this->Article->hasAny(
					array('url' => $article['url'])
			);

			// なければ追加
			if ($result == false) {
				$this->Article->create();
				$this->Article->save($article);
				debug('追加しました' . $article['url']);
			} else {
				debug('追加できませんでした' . $article['url']);
			}
		}
	}


}