<?php

/**
 *
 *
 */
App::uses('AppModel', 'Model');
App::uses('ComponentCollection', 'Controller');
App::uses('CurlMultiComponent', 'Controller/Component');

class TwitterAPIAccessor extends AppModel{

	public $useTable = false;

	// twitter検索APIのURL
	const SEARCH_API_URL = "http://search.twitter.com/search.json";
	// twitterツイート数取得APIのURL
	const COUNT_API_URL = 'http://urls.api.twitter.com/1/urls/count.json?url=';
	// 検索する件数
	const RPP = 30;

	// コンポーネント
	private $curlMulti;

	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		parent::__construct();

		$Collection      = new ComponentCollection();
		$this->curlMulti = new CurlMultiComponent($Collection);
	}

	/**
	 * 特定のURLのツイート数を並列に取得
	 *
	 * @param  array $urls URLの配列
	 * @return array $tweetCounts   URLをキーにしたツイート数の配列
	 *							    array('記事のURL' => ツイート数)
	 */
	public function getTweetCountOfUrls($urls) {

		$reqUrls = array();
		// APIアクセス用のURLの配列を作成
		foreach ($urls as $url) {
			$reqUrl = self::COUNT_API_URL . $url;
			array_push($reqUrls, $reqUrl);
		}

		// 並列にAPIにリクエスト
		$jsons = $this->curlMulti->getContents($reqUrls);

		// ツイート数を配列に取得 array('記事のURL' => ツイート数)
		$tweetCounts = array();
		foreach ($jsons as $i => $json) {
			$data = json_decode($json, true);
			// 最後の / を外す
			$url = $data['url'];
			$url = substr($url, 0, strlen($url) - 1);

			$tweetCounts[$url] = $data['count'];
		}

		return $tweetCounts;
	}

	/**
	 * サイトのURLでツイッターを検索して検索結果からURLを抜き出し、配列形式でを返す
	 *
	 * 検索は並列に行う
	 *
	 * @param  array $sites
	 * @return array $longUrls
	 */
	public function getTweetedUrlsBySearchUrl($urls) {
		$reqUrls = array();
		// URLの配列を作成
		foreach ($urls as $url) {
			$reqUrl = $this->createApiRequestUrl($url);
			array_push($reqUrls, $reqUrl);
		}

		// APIに並列にリクエストして結果をJSON形式で取得
		$jsons = $this->curlMulti->getContents($reqUrls);

		// 検索結果のツイートを配列に取得
		$tweets = array();
		foreach ($jsons as $json) {
			$data = json_decode($json, true);
			$results = $data["results"];

			$tweets = array_merge($tweets, $results);
		}

		$tweetedUrls = array();
		// ツイートの件数ループ
		foreach ($tweets as $tweet) {
			// ツイート内のURLの要素を取得
			$urlRow = end($tweet["entities"]["urls"]);// 2件以上のURLが含まれる場合は最後のURLを取得
			if ( !isset($urlRow["expanded_url"]) ) {
				continue;
			}
			// t.co形式のURLを取リ出す
			$url = $urlRow["expanded_url"];
			// すでに取得したURLと重複している場合はスキップ
			if (array_search($url, $tweetedUrls)) {
				continue;
			}
			// URLを配列に保存
			array_push($tweetedUrls, $url);
		}

		// 短縮URLを展開
		$longUrls = $this->curlMulti->expandUrls($tweetedUrls);

		return $longUrls;
	}

	/**
	 * twitter検索用のURLを作成
	 *
	 * APIのURLにパラメータをくっつける
	 *
	 * @param  string $url
	 * @return string $searchUrl
	 */
	protected function createApiRequestUrl($url) {
		// 1日前の日付を取得
		$yesterday = date('Y-m-d', strtotime('-1 day'));
		// URLを検索用キーワードの形式にフォーマット
		$q = $this->formatUrlForSearch($url);
		// パラメータ設定 今日の日付ツイートを取得 q=○○+since:2012-09-10
		$param = "?rpp=" . self::RPP . "&q={$q}+since:{$yesterday}&include_entities=true";
		// APIのURLとパラメータをくっつける
		$reqUrl = self::SEARCH_API_URL . $param;

		return $reqUrl;
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
	protected function formatUrlForSearch($url) {
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

}