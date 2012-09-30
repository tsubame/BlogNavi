<?php
/**
 * コントローラ
 *
 * ToDo
 * ・並列HTTPアクセスのコンポーネントを作る
 *
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
 * ・cURLって？
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