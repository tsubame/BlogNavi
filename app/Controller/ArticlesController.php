<?php
/**
 * コントローラ
 *
 * ToDo
 * ・コード整形
 * ・バリデーション
 *
 *
 *
 * メモ
 *
 * ・ツイッター検索でのsinceの日付は日本との時差が9時間。
 *   毎日午前9時前に昨日の日付を指定して検索する必要あり
 *
 *
 * 技術的な疑問
 * ・デバッグ時にはdebugを使っていい？
 * ・ログはどうする？
 * ・エラー時の対処法
 *
 */
class ArticlesController extends Controller {

	public $uses  = array('Site', 'TweetSearcher', 'User');
	public $helpers = array('Form');
	public $components = null;
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
	 * 記事の登録
	 */
	public function insert() {

	}

	/**
	 * twitterでサイトのURLを検索し、記事のURLを取得してDBに登録
	 *
	 */
	public function searchTweet() {
		$this->TweetSearcher->exec();

		$this->render('index');
	}

	/**
	 * テスト
	 */
	public function test() {

		$this->render('index');
	}

	public function phpinfo() {
		phpinfo();
	}

}