<?php
/**
 * コントローラ
 *
 *
 * （ToDo）
 *
 * ・ctpファイルを作りこむ
 *   ・編集画面
 *
 * ・ajax
 *
 * ・googleからの登録ロジックを考える
 *
 *
 *
 *
 * ・バリデーション
 * ・テストコードを丁寧に書く
 *
 *
 *
 *
 *
 *
 * （技術的な疑問）
 * ・ログはどうする？
 *
 */
class ArticlesController extends Controller {

	/**
	 *
	 * @var array
	 */
	public $uses = array('Site', 'Article');

	/**
	 *
	 * @var array
	 */
	public $helpers = array('Form', 'Html');

	/**
	 *
	 * @var array
	 */
	public $components = null;

	/**
	 *
	 * @var string
	 */
	//public $layout = 'articles';


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