<?php

/**
 * ロジック
 *
 * （注意）TwitterのAPIの返り値の配列の内容はわかりにくいので書いておく必要あり
 *
 *
 * サイトのURLでtwitterを検索して、そのサイト内の記事でツイートされたものを検索する。
 * 取得した記事のURLをarticlesテーブルに保存。
 * （短縮URLなら展開してから保存）
 *
 * サイトのURLは検索用にフォーマットして検索する（httpやwwwが含まれるとうまく検索できない）
 * htttp://www.yahoo.co.jp/ → yahoo.co.jp/
 *
 *
 */
//App::uses('Site','Model');
//App::uses('Article','Model');
App::uses('AppModel', 'Model');
App::uses('HttpSocket', 'Network/Http');
App::uses('ComponentCollection', 'Controller');
App::uses('CurlMultiComponent', 'Controller/Component');

class ArticleInsertAction extends AppModel {

	public $useTable = false;
	// Siteモデル
	private $Site;
	// Articleモデル
	private $Article;
	// Sitesモデルの配列
	private $sites;

	// テーブルに保存するURLの配列
	private $insertUrls = array();

	// 検索した記事の配列
	private $searchedArticles = array();

	// twitter検索APIのURL
	const SEARCH_API_URL = "http://search.twitter.com/search.json";
	// twitterツイート数取得APIのURL
	const COUNT_API_URL = 'http://urls.api.twitter.com/1/urls/count.json?url=';
	// 検索する件数
	private $rpp = 30;

// 使用するのはコンストラクタでOKか？
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
	}

	/**
	 * 処理実行
	 */
	public function exec() {
		$this->sites = $this->Site->getAllSites();
		// ツイッター検索
		$this->searchSingleThread();
		// カウント数取得
		$this->getTweetCountOfArticles();

		/*
		$options = array(
					'atomic' => true,
					'fieldList' => array('url', 'site_id', 'tweeted_count')
				);
		$this->Article->saveMany($this->searchedArticles);
*/

		// 保存前にサイトのタイトル、サイトIDを取得する必要あり

	}

	/**
	 * 検索した記事のツイート数を取得
	 *
	 */
	private function getTweetCountOfArticles() {
		$urls = array();
		// APIアクセス用のURLの配列を作成
		foreach ($this->searchedArticles as $article) {
			$reqUrl = self::COUNT_API_URL . $article['url'];;
			array_push($urls, $reqUrl);
		}
		// 並列にAPIにアクセス
		$jsons = $this->curlMulti->getContents($urls);

		$urlCounts = array();
		foreach ($jsons as $i => $json) {
			$data = json_decode($json, true);
			$this->searchedArticles[$i]['tweeted_count'] = $data['count'];
			// 最後の / を外す
			$url = $data['url'];
			$url = substr($url, 0, strlen($url) - 1);
			$urlCounts[$url] = $data['count'];
		}

		// 降順に並び替え
		$res = arsort($urlCounts, SORT_NUMERIC);

		debug($urlCounts);
		debug($this->searchedArticles);
	}

	/**
	 *
	 */
	public function demo() {
		echo 'demo';
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

		$uniqueUrls  = array();
		// 記事の件数ループ
		foreach ($articles as $article) {
			// すでに取得したURLと重複している場合はスキップ
			if (array_search($article['url'], $uniqueUrls)) {
				continue;
			}

			// URLが登録済みのサイトの記事のアドレスでなければスキップ
			$result = $this->isValidArticleUrl($article['url']);
			if ($result == false) {
				continue;
			}

			// URLを配列に保存
			array_push($uniqueUrls, $article['url']);
			array_push($this->searchedArticles, $article);
		}

		debug($uniqueUrls);
	}


	/**
	 * 検索処理
	 *
	 * 並列処理を行わないロジック
	 *
	 */
	private function searchSingleThreadOld() {
		$tweetedUrls = array();
		$siteIds = array();

		$this->sites = $this->Site->getAllSites();
		// サイトの件数ループ
		foreach ($this->sites as $site) {
			// twitterAPIでサイトのURLが含まれるツイートを検索
			$tweets = $this->searchTwitter($site['url']);
			foreach ($tweets as $tweet) {
				// ツイート内のURLの要素を取得
				$urlRow = end($tweet["entities"]["urls"]);// ['entities']['urls']が複数の要素の配列になってることがある （2件のURL）

				if ( !isset($urlRow["expanded_url"]) ) {
					continue;
				}
				$tweetedUrl = $urlRow["expanded_url"];
				// 重複分を除いてURLを配列に保存
				if ( !array_search($tweetedUrl, $tweetedUrls) ) {
					array_push($tweetedUrls, $tweetedUrl);
					// サイトIDを保存
					array_push($siteIds, $site['id']);
				}
			}

		}
		// 短縮URLを展開して配列に取得
		$longUrls = $this->curlMulti->expandUrls($tweetedUrls);

		// 必要なURLのみ別の配列に入れなおす
		$uniqueUrls  = array();
		foreach ($longUrls as $i => $url) {
			// 重複している場合はスキップ
			if (array_search($url, $uniqueUrls)) {
				continue;
			}
			// 登録済みのサイトの記事でなければスキップ
			$result = $this->isValidArticleUrl($url);
			if ($result == false) {
				continue;
			}

			// URLを配列に保存
			array_push($uniqueUrls, $url);
			// 記事のデータを作成して配列に保存
			$article = array('url' => $url, 'site_id' => $siteIds[$i]);
			array_push($this->searchedArticles, $article);
		}

		debug($uniqueUrls);
		//debug($this->searchedArticles);
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
		$param = "rpp={$this->rpp}&q={$searchKey}+since:{$today}&include_entities=true";

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
		debug('検索キーワード' . $searchKey);

		return $searchKey;
	}


}