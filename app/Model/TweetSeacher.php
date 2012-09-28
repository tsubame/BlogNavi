<?php

App::uses('Site','Model');
App::uses('AppModel', 'Model');
App::uses('HttpSocket', 'Network/Http');

/**
 *
 *
 */
class TweetSearcher extends AppModel{

	private $Site;

	public $name = 'TweetSearcher';
	public $useTable = false;

	// 検索する件数
	private $rpp = 30;
	// twitterAPIのURL
	const API_URL = "http://search.twitter.com/search.json";


	/**
	 * コンストラクタは？
	 *
	 */

	/**
	 *
	 */
	public function demoTest() {

	}

	/**
	 * 処理実行
	 */
	public function exec() {
		searchSingleThread();
			// URLを配列に入れる（同じURLは入れない）

			// 短縮URLの場合は展開（コンポーネント化）

			// 取得したURLをarticlesテーブルに登録
	}

	/**
	 * 並列処理を行わないロジック
	 *
	 */
	private function searchSingleThread() {
		// サイトをDBから取得
		$sites = $this->Site->getAllSites();
		// サイトの件数ループ
		foreach ($sites as $site) {
			// twitterAPIでURLを検索 24時間以内 100件
			$q    = $this->formatUrlForSearch($site['url']);
// メソッドでJSONもデコードすべき
			$json = $this->searchByJson($q);
			$data = json_decode($json, true);
			$tweets = $data["results"];
// ここまでメソッドで
			debug('検索キーワード' . $q);
			// 結果のツイートの件数ループ
			foreach ($tweets as $tweet) {
				// ['entities']['urls']が複数の要素の配列になってることがある （2件のURL）
				$tweetedUrl = end($tweet["entities"]["urls"]);
				if (isset($tweetedUrl["expanded_url"])) {
					$url = $tweetedUrl["expanded_url"];
					$url = $this->pickUpUrl($url);

echo $url . '<br /><br />' ;
				// 短縮URLではない
				} else {
					debug('短縮URLではない' . $tweet["entities"]);
					continue;
				}
			}
		} // end foreach サイトの件数ループ
	}

	/**
	 * ツイッターを検索して結果をJSON形式で取得
	 *
	 * @param  string $q 	検索キーワード
	 * @return string $json JSONデータ
	 */
	private function searchByJson($q) {
		$socket = new HttpSocket();
// 24時間以内の条件も入れる必要あり
// until=～～
		$param = "rpp=" . $this->rpp . "&q=" . $q . "&include_entities=true";
		$res   = $socket->get(self::API_URL, $param);
		$json  = $res["body"];

		return $json;
	}

	/**
	 * URLからhttp://と最後のファイル名、?以降を取り除く
	 *
	 * @param  string $url
	 * @return
	 */
	private function formatUrlForSearch($url) {
		// www.も取り除く必要あり？
		// ?以降を除く
		if (preg_match('/^http:\/\/[\w\.\/\-_=]+/', $url, $matches)) {
			$url = $matches[0];
		}
		if (preg_match('/^http:\/\/([\w\.\/\-_=]+\/)[\w\-\._=]*$/', $url, $matches)) {
			return $matches[1];
		}

		return $url;
	}

	/**
	 * テキストからURLを抜き出す
	 *
	 * @param  string  $text
	 * @param  boolean $includeParam true ?以降を含める
	 * @return string  URL
	 */
	private function pickUpUrl($text, $includeParam = false) {
		// ?以降を含めない場合
		if ($includeParam == false) {
			$pattern = '/^http:\/\/[\w\.\-\/_=]+/';
		} else {
			$pattern = '/^http:\/\/[\w\.\-\/_=?&@:]+$/';
		}

		if (preg_match($pattern, $text, $matches)) {
			return $matches[0];
		}

		return null;
	}

}
