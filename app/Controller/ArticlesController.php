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


		$this->render('index');
	}


	/**
	 * テスト
	 */
	public function test() {
		$this->TweetSearcher->exec();

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