<?php
/**
 * コントローラ
 *
 * ToDo
 * ・コード整形
 *
 * ・バリデーション
 * ・RSSで記事取得
 * ・サーバにアップロードして運用
 * ・phpmyadminインストール
 *
 *
 * 今の方式の問題点
 *
 * ・かなり前の日付の記事まで拾ってくる
 * ・記事以外のURLまで拾ってくる
 * ・タイトルにサイト名が入る
 * ・記事の日付がわからない
 *
 *
 * RSSで取得する流れ
 *
 * ・各サイトのRSSを登録しておく
 *
 * ・各サイトのRSSフィードにアクセス
 *
 *
 *
 *
 *
 *
 *
 * 技術的な疑問
 * ・ログはどうする？
 * ・エラー時の対処法
 * 　→エラーが複数発生する場合はエラーコードなど作る
 * 　それ以外はfalseを返すようにする
 *
 */
class ArticlesController extends Controller {

	/**
	 *
	 * @var unknown_type
	 */
	public $uses  	= array('Site', 'Article');

	/**
	 *
	 * @var unknown_type
	 */
	public $helpers = array('Form', 'Html');

	/**
	 *
	 * @var unknown_type
	 */
	public $components = null;

	/**
	 *
	 * @var unknown_type
	 */
	public $layout = 'articles';


	/**
	 * 記事の一覧
	 */
	public function index() {

		if (isset($this->passedArgs[0])) {
			$categoryId = $this->passedArgs[0];
		} else {
			$categoryId = null;
		}

		$results = $this->Article->selectTodaysArticles($categoryId);

		$this->set('results', $results);
	}

	/**
	 *
	 */
	public function insertAndUpdate() {
		$this->insert();
		$this->update();

		$this->render('update');
	}

	/**
 	 * 記事の登録
 	 *
 	 */
	public function insert() {
		$insertAction = ClassRegistry::init('ArticleInsertAction');
		$insertAction->exec();

		$this->update();
	}

	/**
	 * ツイート数を取得して記事を更新
	 */
	public function update() {
		$action = ClassRegistry::init('ArticleUpdateAction');
		$action->exec();
	}

	/**
	 * ツイート数を取得
	 */
	public function getTweetCount() {
		$action = ClassRegistry::init('ArticleGetTweetCountAction');
		$action->exec();
	}


}