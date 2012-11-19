<?php
/**
 * コントローラ
 *
 *
 * （ToDo）
 *
 * //・登録フォームを使いやすく
 *
 * ・ツイート数の多い記事を見つけたらサイトを登録
 *
 * ・facebook連携
 *
 * //・記事を自動的に削除 各ジャンル1日10件 1日1回実行
 *
 * ・ロギング log4phpの利用
 *
 * ・定数を外のクラスに出す
 *
 * //・カテゴリをDBから拾ってくる
 *
 * //・未登録サイトをすべて登録
 *
 * ・DBのテストの仕方を覚える
 *
 * ・fixtureを使う
 *
 * ・スポーツナビブログからの記事登録
 *   RSSを取得してカウント数を
 *
 * ・削除済みのサイトを表示
 *
 * ・未登録サイトをすべて登録
 *
 * ・登録画面で名前とフィードURLを入力できるように
 *
 * ・ブログランキングから登録したサイトを全て削除
 *
 * ・ctpファイルを作りこむ
 *   ・編集画面
 *
 * ・ajax
 *
 * ・yahoo検索からの登録ロジックを考える
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

	// データベース
	public $uses = array('Article');

	// ヘルパー
	public $helpers = array('Form', 'Html');

	// コンポーネント
	public $components = null;


	/**
	 * 記事の一覧
	 *
	 */
	public function index() {

		debug('test');

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