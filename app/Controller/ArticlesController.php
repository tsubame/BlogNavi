<?php
/**
 * コントローラ
 *
 *
 * メモ
 * ・コンポーネントには他のアプリでも使えるものを
 * ・それ以外の独自処理はモデルまたはvendorで
 *
 * ・ツイッターの検索は　http://を抜いて検索する必要あり？
 *
 *
 * 技術的な疑問
 * ・デバッグ時にはdebugを使っていい？
 * ・ログはどうする？
 * ・エラー時の対処法
 *
 */
class ArticlesController extends Controller {

	//public $components = array('Http');
	public $uses  = array('Site', 'TweetSearcher', 'User');
	public $helpers = array('Form');

	//public $layout = 'articles';

	/**
	 * 記事の一覧
	 */
	public function index() {

		$datas = $this->Site->find('all');
		$this->set('datas', $datas);
		debug($datas);
	}

	/**
	 * twitterでサイトのURLを検索し、記事のURLを取得してDBに登録
	 *
	 */
	public function searchTweet() {
	// 並列処理を行わないロジック

		// サイトをDBから取得
		$sites = $this->Site->getAllSites();
		// サイトの件数ループ
		foreach ($sites as $site) {
			// twitterAPIでURLを検索 24時間以内 100件
//
$q = $this->formatUrlForSearch($site['url']);

			$json = $this->searchByJson($q);
			$data = json_decode($json, true);
			$tweets = $data["results"];
debug('検索キーワード' . $q);
			// 結果のツイートの件数ループ
			foreach ($tweets as $tweet) {
//echo $tweet["text"] . '<br />';
				// t.co短縮URLを展開
//debug($tweet['entities']['urls']);

// ['entities']['urls']が複数の要素の配列になってることがある （2件のURL）
$tweetedUrl = end($tweet["entities"]["urls"]);
//debug($tweetedUrl);
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

			// 帰ってきたJSONデータをデコード

			// JSONデータの中のツイートの件数ループ

			// URLを配列に入れる（同じURLは入れない）

			// 短縮URLの場合は展開（コンポーネント化）

			// 取得したURLをarticlesテーブルに登録

		}


		$this->render('index');
	}


	/**
	 * テスト
	 */
	public function test() {
		$this->User->test();

		debug();
		$this->render('index');
	}

	/**
	 * ツイッターを検索して結果をJSON形式で取得
	 *
	 * @param  string $q 	検索キーワード
	 * @return string $json JSONデータ
	 */
	private function searchByJson($q) {
		App::uses('HttpSocket', 'Network/Http');
		// 検索する件数
		$rpp = 30;
// 24時間以内の条件も入れる必要あり
		$apiUrl = "http://search.twitter.com/search.json";
		$param = "rpp=" . $rpp . "&q=" . $q . "&include_entities=true";

		$socket = new HttpSocket();
		// twitterで検索
		$response = $socket->get($apiUrl, $param);
		$json = $response["body"];

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