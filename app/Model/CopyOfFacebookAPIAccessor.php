<?php

App::uses('ComponentCollection', 'Controller');
App::uses('CurlComponent', 'Controller/Component');

/**
 * Facebookから特定のサイトの「シェア」の数を取得するクラス
 *
 *
 * 仕様
 *
 * http://graph.facebook.com/のあとにURLをつけてアクセスするとJSONデータが帰ってくる。（要URLエンコード）
 * JSONデータの'shares'の要素がfacebookのシェアの数。
 *
 *
 * 依存クラス
 *
 * ・CurlComponent
 * ・ComponentCollection
 *
 * エラー
 *
 */
class FacebookAPIAccessor extends AppModel{

	/**
	 * テーブルの使用
	 *
	 * @var bool
	 */
	public $useTable = false;

	/**
	 * Curlコンポーネント
	 *
	 * @var object CurlComponent
	 */
	private $Curl;

	/**
	 * シェア数取得APIのURL
	 *
	 * @var string
	 */
	const GRAPH_API_URL = 'http://graph.facebook.com/';


	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		parent::__construct();

		$Collection = new ComponentCollection();
		$this->Curl = new CurlComponent($Collection);
	}

	/**
	 * 単一のサイトの「シェア」数を取得する
	 *
	 * @param  string $url
	 * @return int    $count
	 */
	public function getShareCount($url) {
		$apiUrl = self::GRAPH_API_URL . urlencode($url);
		// APIにアクセスしてJSONデータ取得
		$json  = $this->Curl->getContent($apiUrl);
		$row   = json_decode($json, true);
		// 'shares' の要素を数字に直して取り出す
		$count = (int)$row['shares'];

		return $count;
	}

	/**
	 * 複数のサイトの「いいね」数を取得する
	 *
	 * @param  array  $urls   URLの配列
	 * @return int    $counts URLをキーにしたシェア数の配列
	 * 							array('URL' => シェア数)
	 */
	public function getShareCountOfUrls($urls) {
		// URLの配列作成
		$reqUrls = array();
		foreach ($urls as $url) {
			$reqUrls[] = self::GRAPH_API_URL . urlencode($url);
		}

		// 並列にアクセス
		$jsons = $this->Curl->getContents($reqUrls);

		$counts = array();
		foreach ($jsons as $json) {
			// JSONデコード
			$row   = json_decode($json, true);
			// URLとシェア数を取り出す
			if ( isset($row['shares'])) {
				$count  = (int)$row['shares'];
			} else {
				$count = 0;
			}

			$key = (string)$row['id'];
			$counts[$key] = $count;
		}

		return $counts;
	}

}