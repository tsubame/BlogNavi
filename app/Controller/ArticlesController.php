<?php
/**
 * コントローラクラス
 *
 */
class ArticlesController extends Controller {

	/**
	 * データベース
	 *
	 * @var array
	 */
	public $uses = array('Article', 'Category');

	/**
	 * ヘルパー
	 *
	 * @var array
	 */
	public $helpers = array('Form', 'Html');


	/**
	 * アクションの前の処理
	 *
	 * カテゴリの取得
	 *
	 * @see Controller::beforeFilter()
	 */
	public function beforeFilter() {
		parent::beforeFilter();

		$categories = Configure::read('Category.names');
		$this->set('categories', $categories);
	}

	/**
	 * 記事の一覧 ユーザ向け
	 *
	 */
	public function index() {
		if (isset($this->passedArgs[0])) {
			$categoryId = (int) $this->passedArgs[0];
		} else {
			$categoryId = null;
		}
		$results = $this->Article->selectTodaysArticles($categoryId);

		$this->set('results', $results);
	}

// view未実装
	/**
	 * 記事の一覧 管理者向け
	 *
	 */
	public function editIndex($categoryId = null) {
		$results = $this->Article->selectTodaysArticles($categoryId);
		$this->set('results', $results);
	}

	/**
	 * RSSから記事の登録
	 *
	 */
	public function register() {
		$action = ClassRegistry::init('ArticleRegisterAction');
		$action->exec();

		$this->getShareCount();
	}

	/**
	 * ツイート数を取得して記事を更新
	 */
	public function getShareCount() {
		$action = ClassRegistry::init('ArticleGetShareCountAction');
		$action->exec();

		$this->render('editIndex');
	}

	/**
	 * 1つの記事を削除
	 *
	 */
	public function delete() {

	}

	/**
	 * 過去の記事を自動的に削除
	 *
	 */
	public function deletePastArticles() {
		$this->Article->deletePastArticles();
	}







// 以下、削除予定

	/**
	 * 記事を取得とツイート数の取得を同時に
	 *
	 */
/*
	public function insertAndUpdate() {
		$this->insert();
		$this->update();

		$this->render('update');
	}
*/
	/**
 	 * RSSから記事の登録
 	 *
 	 */
// 削除予定
	public function insert() {
		$this->register();
	}

	// 要名前変更 → getShareCunts
	/**
	 * ツイート数を取得して記事を更新
	 *
	 */
	public function update() {
		$this->getShareCount();
	}

// かぶってない？


	/**
	 * ツイート数を取得
	 *
	 */
/*
	public function getTweetCount() {
		$action = ClassRegistry::init('ArticleGetTweetCountAction');
		$action->exec();
	}
*/

}