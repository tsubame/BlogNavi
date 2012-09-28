<?php


/**
 * 技術的な課題
 *
 * ・出来ればeclipseプラグインからGitHubを使いたい
 *
 *
 *
 * 疑問点
 *
 *  シングルクオートとダブルクオートの使い分けをどうするか
 *
 *
 *
 * 実装上の注意
 * ・関数には引数の型チェックを
 *
 *
 * DB
 * ・articlesテーブル
 * id
 * title
 * url
 * site_id
 * tweeted_count
 *
 *
 * ・sitesテーブル
 * id		integer
 * name		varchar
 * url		varchar
 * rss_url	varchar
 * category		varchar
 * enabled	boolean
 *
 *
 *
 *
 */

/**
 * cakePHP コードのデモ
 *
 *
 *
 * @package       app.Controller
 */



class DemoController extends Controller {

	public $name = "Demo";

	/**
	 *
	 */
	public function beforeFilter() {
		App::uses('HttpSocket', 'Network/Http');
		App::uses('Xml', 'Utility');
	}

	/**
	 *
	 *
	 */
	public function index() {



	}

	/**
	 *　ツイッター検索デモ
	 *
	 */
	public function test() {

		$q = "sportsnavi.yahoo.co.jp";
		$q = "sportsnavi";
		//$xml = searchByAtom($q);
		// JSONで検索
		$json = $this->searchByJson($q);
		$data = json_decode($json, true);
		$results = $data["results"];
		// 配列初期化
		$tweetCounts = array();
		// テスト用出力
		foreach ($results as $entry) {
			echo $entry["text"];
			echo "<br />";
			if (isset($entry["entities"]["urls"][0]["expanded_url"])) {
				$url =  $entry["entities"]["urls"][0]["expanded_url"];
				$url = $this->getUrlByPreg($url);
				echo $url;
			} else {
				echo "■expanded URLなし。<br />";
				print_r($entry["entities"]);
				continue;
			}
			echo "<br /><br />";

			if (! isset($tweetCounts[$url])) {
				$tweetCounts[$url] = 0;
			}
			$tweetCounts[$url] ++ ;
		}

		arsort($tweetCounts, SORT_NUMERIC);
		print_r($tweetCounts);

		foreach ($tweetCounts as $url => $count) {
			echo "<br /><br />";
			// 文字数が小さければ短縮URL展開
			if (strlen($url) < 30) {
				echo " 短縮前：$url ";
				$url = $this->getLongUrl($url);
				$url = $this->getUrlByPreg($url);
				echo " 短縮後： ";
			}
			echo $count . " : " . $url;

		}

	// ロジック
	// ・ツイッターから記事のURLを取得

		// DBからサイトを取得
		// サイトの件数ループ
			// サイトのURLをすべて配列に入れる

		// twitterでサイトのURLを検索 24時間以内 100件 並列処理
		// JSONデータの配列が返ってくる

		// JSONデータの件数ループ
			// ツイートの件数ループ
				// expansion_urlを取得
				// 取得したURLを配列に追加

		// 短縮URLをすべて展開（並列処理）

		// URLをDBに登録

	}

	/**
	 * サイトの登録
	 *
	 * ・手動
	 * 　主要なメディアは手動で登録
	 *
	 * ・自動
	 * 　googleからサイトを登録
	 *
	 */
	private function registerSites() {

		//

	}

	/**
	 *
	 * @param $q search keyword
	 * @return $json json data.
	 */
	private function searchByJson($q) {
		$rpp = 30;
		$apiUrl = "http://search.twitter.com/search.json";
		$param = "rpp=" . $rpp . "&q=" . $q . "&include_entities=true";

		$socket = new HttpSocket();
		// twitterで検索
		$response = $socket->get($apiUrl, $param);
		$json = $response["body"];

		return $json;
		//$xmlArray = Xml::toArray(Xml::build($xml));
	}


	/**
	 *
	 * @param $q search keyword
	 */
	private function searchByAtom($q) {
		$rpp = 10;
		$apiUrl = "http://search.twitter.com/search.atom";
		$param = "rpp=" . $rpp . "&q=" . $q . "&include_entities=true";

		$socket = new HttpSocket();
		// twitterで検索
		$response = $socket->get($apiUrl, $param);
		$xml = $response["body"];

		return $xml;
	}


	/**
	 * テキストからURLを抜き出す
	 *
	 * @param string $text
	 * @param boolean $includeParam true ?以降を含める
	 * @return string URL
	 */
	private function getUrlByPreg($text, $includeParam = false) {

		$urlPattern = "/^https?(:\\/\\/[a-zA-Z0-9\\/\\-_\\.?&@]+)$/";
		// ?以降を含めない
		if ($includeParam == false) {
			$urlPattern = "/^https?(:\\/\\/[a-zA-Z0-9\\/\\-_\\.]+)$/";
		}
		if (preg_match($urlPattern, $text, $urls)) {
			return $urls[0];
		}

		return null;
	}

	/**
	 * 短縮URLを展開
	 *
	 * @param unknown_type $short_url
	 * @return string $long_url
	 */
	function getLongUrl($shortUrl){
		if ($shortUrl == "" OR is_null($shortUrl)) {
			return null;
		}

		$longUrl = null;
		// HTTPヘッダ取得
		$h = get_headers($shortUrl, 1);
		if (isset($h['Location'])){
			$longUrl = $h['Location'];
			if (is_array($longUrl)){
				$longUrl = end($longUrl);
			}
		}

		return $longUrl;
	}

}
