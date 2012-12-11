<?php
/**
 * FacebookAPIにアクセスするコンポーネント
 *
 * ・特定のURLのシェア数を取得
 * ・複数のURLのシェア数を並列に取得できる
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

class FbApiAccessorComponent extends Component {

	/**
	 * コンポーネント
	 *
	 * @var array
	 */
	public $components = array('Curl');

	/**
	 * シェア数取得APIのURL
	 *
	 * @var string
	 */
	const GRAPH_API_URL = 'http://graph.facebook.com/';


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
	 * 複数のサイトの「シェア」数を取得する
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