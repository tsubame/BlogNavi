<?php
/**
 * Sitesコントローラ
 *
 *
 * アクション
 *
 * ・サイトの一覧
 *
 * ・サイトの一覧（管理者向け）
 *     カテゴリ別のサイトのリスト
 *     削除済みのサイトは表示しない
 *
 * ・サイトの編集処理 ajax 画面なし
 *
 * ・未登録サイトの一覧
 *     削除済みのサイトは表示しない
 *
 * ・サイト登録フォーム
 *
 * ・サイトの登録処理 画面はなし
 *     登録フォームを表示
 *
 * ・サイトをファイルから登録
 *
 * ・サイトを削除 ajax 画面なし
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
	 * アクションの前の処理
	 *
	 * カテゴリの取得
	 *
	 * @see Controller::beforeFilter()
	 */
	public function beforeFilter() {
		parent::beforeFilter();

		$categories = $this->Category->getCategoryNames();
		$this->set('categories', $categories);
	}

	/**
	 * サイトの一覧を表示 ユーザ向け
	 *
	 */
	public function index($categoryId = null) {
		$sites = $this->Site->getSites($categoryId);
		$this->set('sites', $sites);
	}

	/**
	 * サイトの一覧を表示 管理者向け
	 *
	 */
	public function editIndex($categoryId = null) {
		$sites = $this->Site->getSites($categoryId);
		$this->set('sites', $sites);
	}

	/**
	 * サイトの編集
	 * このアクションにはAjaxでのみアクセス可
	 *
	 */
	public function update() {
		if ( !isset($this->request->data)) {
			$this->redirect(array('action' => 'index'));

			return;
		}
		$site = $this->request->data;

		if (isset($site['id'])) {
			$this->Site->save($site);
		}

		$this->render('edit_index');
	}

	/**
	 * サイトの削除
	 * このアクションにはAjaxでのみアクセス可
	 *
	 */
	public function delete() {
		if ( !isset($this->request->data)) {
			$this->redirect(array('action' => 'index'));

			return;
		}
		$site = $this->request->data;

		if (isset($site['id'])) {
			$site['is_deleted'] = true;
			$this->Site->save($site);
			//$this->Site->checkDeleted($site);
		}

		$this->render('edit_index');
	}

	/**
	 * サイト登録フォームを表示
	 *
	 */
	public function registerForm() {
		$this->render('register_form');
	}

	/**
	 * サイトの登録
	 * 登録フォームからサイトを登録する
	 *
	 */
	public function register() {
debug('デバッグ');
echo 'test';

		if ( !isset($this->data['Site'])) {
			$this->redirect(array('action' => 'registerForm'));
		}
		$site = $this->data['Site'];
debug($site);

		$action = ClassRegistry::init('SiteRegisterAction');
		$action->exec($site);

		$this->render('registerForm');
	}

	/**
	 * サイトをブログランキングから登録
	 *
	 */
	public function registerFromRank() {
		$action = ClassRegistry::init('SiteRegisterFromRankAction');
		$action->exec();

		$this->render('unregi_site_index');
	}

	/**
	 * サイトをファイルから登録
	 *
	 */
	public function registerFromFile() {
		$action = ClassRegistry::init('SiteRegisterFromFileAction');
		$action->exec();

		$this->render('unregi_site_index');
	}

	/**
	 * サイトをスポーツナビ+のRSSから登録
	 *
	 */
	public function registerFromSNavi() {
		$action = ClassRegistry::init('SiteRegisterFromSNaviAction');
		$action->exec();

		$this->render('unregi_site_index');
	}

// アクション未実装
	/**
	 * ヤフー検索からサイトを登録
	 */
	public function registerFromYahoo() {
		$action = ClassRegistry::init('SiteRegisterFromYahooAction');
		$action->exec();

		$this->render('unregi_site_index');
	}

	/**
	 * 未登録サイトの一覧を表示
	 *
	 */
	public function unregiSiteIndex() {
		$sites = $this->Site->getUnRegiSites();
		$categories = $this->Category->getCategoryNames();

		$this->set('sites', $sites);
		$this->set('categories', $categories);
	}

	/**
	 * 未登録サイトをすべて登録
	 *
	 */
	public function registerAll() {
		$this->Site->registerAll();
		$this->render('unregi_site_index');
	}

	/**
	 * サイトのFacebookシェア数を取得
	 *
	 */
	public function getShareCount() {
		$action = ClassRegistry::init('SiteGetLikeCountAction');
		$action->exec();
	}

}