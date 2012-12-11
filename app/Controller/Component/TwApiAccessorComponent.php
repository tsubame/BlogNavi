<?php
/**
 * twitterAPIにアクセスするコンポーネント
 *
 * ・特定のURLのRT数を取得
 * ・複数のURLのRT数を並列に取得できる
 *
 *
 * 依存クラス
 * ・CurlComponent
 *
 * エラー時の処理
 * ・？
 * ・不正なURL、404等の場合で取得できないとき → RT数に0が入る
 *
 */

class TwApiAccessorComponent extends Component {

	/**
	 * コンポーネント
	 *
	 * @var array
	 */
	public $components = array('Curl');

	/**
	 * twitterツイート数取得APIのURL
	 * このURLのあとにURLを付けてアクセスする
	 *
	 * @var string
	 */
	const COUNT_API_URL = 'http://urls.api.twitter.com/1/urls/count.json?url=';


	/**
	 * 複数のURLのRT数を並列に取得
	 *
	 * APIにアクセスしてJSONデータを取得し、そこからRT数を抜き出す
	 *
	 * @param  array $urls URLの配列
	 * @return array $rtCounts   URLをキーにしたツイート数の配列
	 *							    array('記事のURL' => RT数)
	 */
	public function getTweetCountOfUrls($urls) {
		// APIアクセス用のURLの配列を作成
		$reqUrls = array();
		foreach ($urls as $url) {
			$reqUrl = self::COUNT_API_URL . $url;
			array_push($reqUrls, $reqUrl);
		}

		// 並列にAPIにリクエスト
		$jsons = $this->Curl->getContents($reqUrls);

		// RT数を配列に取得
		$rtCounts = array();
		foreach ($jsons as $i => $json) {
			$data = json_decode($json, true);
			// 最後の / を外す
			$url = $data['url'];
			$url = substr($url, 0, strlen($url) - 1);

			$rtCounts[$url] = $data['count'];
		}

		return $rtCounts;
	}

	/**
	 * 単一のURLのRT数を取得
	 *
	 * APIにアクセスしてJSONデータを取得し、そこからRT数を抜き出す
	 *
	 * @param  string $url  URL
	 * @return int    $rtCount   URLをキーにしたツイート数の配列
	 *					  	    	 array('記事のURL' => ツイート数)
	 */
	public function getTweetCountOfUrl($url) {
		$urls = array($url);

		$rtCounts = $this->getTweetCountOfUrls($urls);

		$rtCount = array_pop($rtCounts);

		return $rtCount;
	}
}