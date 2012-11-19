<?php
/**
 * コントローラ
 *
 *
 * メモ
 * ・コンポーネントには他のアプリでも使えるものを
 * ・それ以外の独自処理はモデルまたはvendorで
 *
 *
 * アクション
 *
 * ・編集画面
 *     カテゴリ別のサイトのリスト
 *     削除済みのサイトは表示しない
 *
 * ・編集処理 ajax 画面はなし
 *
 * ・未カテゴライズサイトの一覧画面
 *     削除済みのサイトは表示しない
 *
 * ・登録画面
 *
 * ・登録処理 画面はなし
 *     登録フォームを表示
 *
 * ・サイトを自動登録
 *
 * ・削除処理 ajax 画面はなし
 *
 */
class SitesController extends Controller {

	/**
	 * コンポーネント
	 *
	 * @var array
	 */
	public $components = array();

	/**
	 * ヘルパー
	 *
	 * @var array
	 */
	public $helpers = array('Form');

	/**
	 * レイアウト
	 *
	 * @var array
	 */
	public $layout = 'default';

	/**
	 * モデル
	 *
	 * @var array
	 */
	public $uses = array('Site', 'Category');


	/**
	 * サイトの一覧
	 *
	 */
	public function index($categoryId = null) {

		$sites = $this->Site->getSites($categoryId);

		$this->set('sites', $sites);
	}

	/**
	 * 編集画面 サイトの一覧
	 *
	 * @param string $categoryId
	 */
	public function editForm($categoryId = null) {
		$sites = $this->Site->getSites($categoryId);
		$this->set('sites', $sites);

		$categories = $this->Category->getCategoryNames();
		$this->set('categories', $categories);
	}

	/**
	 * 編集処理 Ajax
	 *
	 */
	public function edit() {
		if ( !isset($this->request->data)) {
			$this->redirect(array('action' => 'editList'));

			return;
		}
		$site = $this->request->data;

		if (isset($site['id'])) {
			$this->Site->save($site);
		}

		$this->render('editForm');
	}

	/**
	 * 未登録サイトの一覧
	 * 編集画面
	 *
	 */
	public function unregiList() {
		$sites = $this->Site->getUnRegiSites();
		$categories = $this->Category->getCategoryNames();

		$this->set('sites', $sites);
		$this->set('categories', $categories);
	}

	/**
	 * 登録フォーム
	 *
	 */
	public function registerForm() {
		// カテゴリ取得
		$categories = $this->Category->getCategoryNames();

		$this->set('categories', $categories);
	}

	/**
	 * 登録処理
	 * フォームからサイトを登録
	 *
	 */
	public function register() {
		if ( !isset($this->data['Site'])) {
			$this->redirect(array('action' => 'registerForm'));
		}
		$site = $this->data['Site'];

		$action = ClassRegistry::init('SiteRegisterAction');
		$action->exec($site);

		$this->render('registerForm');
	}

	/**
	 * サイトを自動登録
	 */
	public function registerAuto() {
		$action = ClassRegistry::init('SiteRegisterAutoAction');
		$action->exec();

		$this->render('unregiList');
	}

	/**
	 * サイトをファイルから登録
	 */
	public function registerFromFile() {
		$action = ClassRegistry::init('SiteRegisterFromFileAction');
		$action->exec();

		$this->render('unregiList');
	}

	/**
	 * 未登録サイトをすべて登録
	 */
	public function registerAll() {

		$this->Site->registerAll();

		$this->render('unregiList');
	}

	/**
	 * 削除
	 *
	 */
	public function delete() {
		if ( !isset($this->request->data)) {
			$this->redirect(array('action' => 'editList'));

			return;
		}

		$site = $this->request->data;

		if (isset($site['id'])) {
			$this->Site->checkDeleted($site);
		}

		$this->render('editForm');
	}

}