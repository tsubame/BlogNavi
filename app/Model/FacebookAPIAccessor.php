<?php

/**
 *
 *
 */
App::uses('AppModel', 'Model');
App::uses('ComponentCollection', 'Controller');
App::uses('CurlMultiComponent', 'Controller/Component');

/**
 * Facebookから特定のサイトの「いいね！」の数を取得するクラス
 *
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
	 * 「いいね！」数取得APIのURL
	 *
	 * @var string
	 */
	const GRAPH_API_URL = 'http://graph.facebook.com/';

	/**
	 * CurlMultiコンポーネント
	 *
	 * @var object CurlMultiComponent
	 */
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
	 * 特定のサイトの「いいね」数を取得する
	 *
	 * @param  string $url
	 * @return int    $count
	 */
	public function getLikeCount($url) {

		$apiUrl = self::GRAPH_API_URL . urlencode($url);

		$json = $this->curlMulti->getContent($apiUrl);
		$row  = json_decode($json, true);
		$count = (int)$row['shares'];

		return $count;
	}

	/**
	 * 複数のサイトの「いいね」数を取得する
	 *
	 * @param  array  $urls
	 * @return int    $counts URLをキーにしたいいね数の配列
	 * 							array('URL' => いいね数)
	 */
	public function getLikeCountOfUrls($urls) {
		$reqUrls = array();
		foreach ($urls as $url) {
			$reqUrls[] = self::GRAPH_API_URL . urlencode($url);
		}

		// 並列にアクセス
		$jsons = $this->curlMulti->getContents($reqUrls);

		$counts = array();
		foreach ($jsons as $json) {
			// JSONデコード
			$row   = json_decode($json, true);
			// URLといいね数を取り出す
			$count  = (int)$row['shares'];
			$key    = (string)$row['id'];

			$counts[$key] = $count;
		}

		return $counts;
	}

}