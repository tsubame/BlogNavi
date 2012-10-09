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
App::uses('Site','Model');
App::uses('Article','Model');
App::uses('AppModel', 'Model');
App::uses('HttpSocket', 'Network/Http');
App::uses('ComponentCollection', 'Controller');
App::uses('CurlMultiComponent', 'Controller/Component');

class TweetSearcher extends AppModel{

	public $useTable = false;
	// Siteモデル
	private $Site;
	// Articleモデル
	private $Article;

	// テーブルに保存するURLの配列
	private $insertUrls = array();

	// articlesテーブルに保存するデータの配列 Articleモデル
	private $insertArticles = array();

// 定数クラスに移す
	// twitterAPIのURL
	const API_URL = "http://search.twitter.com/search.json";
	// 検索する件数
	private $rpp = 30;

// 使用するのはコンストラクタでOKか？
	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		parent::__construct();
		$this->Site = new Site();
		$this->Article = new Article();
// この書き方でOK?
		$Collection = new ComponentCollection();
		$this->curlMulti = new CurlMultiComponent($Collection);
	}

	/**
	 * 処理実行
	 */
	public function exec() {
		$this->searchSingleThread();

		// テーブルに保存
		$this->Article->save($this->insertArticles);

		// 保存前にサイトのタイトル、サイトIDを取得する必要あり

	}

	/**
	 * 検索処理
	 *
	 * 並列処理を行わないロジック
	 *
	 */
	private function searchSingleThread() {
		$tweetedUrls = array();
		$siteIds = array();

		$sites = $this->Site->getAllSites();
		// サイトの件数ループ
		foreach ($sites as $site) {
			// twitterAPIでサイトのURLが含まれるツイートを検索
			$tweets = $this->searchByJson($site['url']);

			foreach ($tweets as $tweet) {
				// ツイート内のURLの要素を取得
				$urlEntity = end($tweet["entities"]["urls"]);// ['entities']['urls']が複数の要素の配列になってることがある （2件のURL）
				if ( !isset($urlEntity["expanded_url"]) ) {
					continue;
				}
				$tweetedUrl = $urlEntity["expanded_url"];

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
		// 重複分を除いて別の配列に入れなおす
		$insertUrls  = array();

		foreach ($longUrls as $i => $url) {
			if ( !array_search($url, $insertUrls) ) {
				array_push($insertUrls, $url);

				// 記事のデータを作成して配列に保存
				$article = array('url' => $url, 'site_id' => $siteIds[$i]);
				array_push($this->insertArticles, $article);
			}
		}

		debug($insertUrls);
		debug($this->insertArticles);
	}


// 配列のデータ形式も書く
	/**
	 * ツイッターを検索して結果をJSON形式で取得
	 *
	 * 検索前にURLを検索用の文字列にフォーマットする
	 * 取得したJSONデータはデコードして配列で返す
	 *
	 * @param  string $url 	  URL
	 * @return array  $tweets 配列
	 */
	private function searchByJson($url) {
		$socket = new HttpSocket();

		// URLを検索用キーワードの形式にフォーマット
		$searchKey = $this->formatUrlForSearch($url);

// 24時間以内にしたいがこれでいいか？
		// 本日の日付を取得
		//$now = getdate();
		$today = date('Y-m-d', strtotime('-1 day'));

		// パラメータ設定 今日の日付ツイートを取得 q=○○+since:2012-09-10
		$param = "rpp={$this->rpp}&q={$searchKey}+since:{$today}&include_entities=true";

echo date('Y-m-d H:i:s');
echo $param;
		// GETリクエスト
		$res   = $socket->get(self::API_URL, $param);
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