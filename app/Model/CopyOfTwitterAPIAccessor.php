<?php

App::uses('CurlComponent', 'Controller/Component');

/**
 * ツイッターのAPIにアクセスするクラス
 *
 * ・特定のURLのリツイート数を取得
 *
 *
 * 依存クラス
 * ・Component/CurlComponent
 * ・ComponentCollection
 *
 * エラー処理
 * ・
 */
class TwitterAPIAccessor extends AppModel{

	/**
	 * 同名のテーブルの使用
	 *
	 * @var unknown_type
	 */
	public $useTable = false;

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
	 *							    array('記事のURL' => ツイート数)
	 */
	public function getTweetCountOfUrls($urls) {
		// APIアクセス用のURLの配列を作成
		$reqUrls = array();
		foreach ($urls as $url) {
			$reqUrl = self::COUNT_API_URL . $url;
			array_push($reqUrls, $reqUrl);
		}

		$collection = new ComponentCollection();
		$curl  = new CurlComponent($collection);
		// 並列にAPIにリクエスト
		$jsons = $curl->getContents($reqUrls);

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
	 * @return array  $rtCounts   URLをキーにしたツイート数の配列
	 *					  	    	 array('記事のURL' => ツイート数)
	 */
	public function getTweetCountOfUrl($url) {
		$urls = array($url);

		return $this->getTweetCountOfUrls($urls);
	}


}