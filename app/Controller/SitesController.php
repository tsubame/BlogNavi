<?php
/**
 * コントローラ
 *
 *
 * メモ
 * ・コンポーネントには他のアプリでも使えるものを
 * ・それ以外の独自処理はモデルまたはvendorで
 *
 */
class SitesController extends Controller {

	//public $name = "Sites";

	public $components = array('HttpUtil');
	public $helpers = array('Form');
	public $layout = 'sites';

	/**
	 * サイトの一覧
	 */
	public function index() {

		$results = $this->Site->find('all');

		$sites = array();
		foreach ($results as $data) {
			$site = $data['Site'];

			array_push($sites, $site);
		}

		$this->set('sites', $sites);
	}

	/**
	 * 登録フォームから手動でサイトを登録
	 *
	 * 登録前にURLからサイトの名前を取得する
	 *
	 */
	public function register() {
		if (! isset($this->data['Site'])) {
			return;
		}

		// URLの最後がファイル名ならファイル名を削除
		$site = $this->data['Site'];

		// ファイル名を取り除く
		if (preg_match('/^(http:\/\/[\w\.\/\-_=]+\/)[\w\-\._=]*$/', $site['url'], $matches)) {
			$site['url'] = $matches[1];
		}

		$site['name'] = $this->HttpUtil->getSiteName($site['url']);
		debug($site);
		// 登録
		$this->Site->save($site);

		$this->render('registerForm');

		// Viewに変数として渡すべき
		debug ("{$site['name']} : {$site['url']} を登録しました。");
	}

	/**
	 * 登録フォーム
	 *
	 */
	public function registerForm() {

	}

	/**
	 * googleから登録
	 */
	public function registerByGoogle() {


	}

	/**
	 * アップデート*
	 *
	 */
	public function update() {
		if (! isset($this->data['Site'])) {
			return;
		}

		// URLの最後がファイル名ならファイル名を削除
		$site = $this->data['Site'];


		$this->Site->save($site);

		debug($site);

		$this->render('index');
	}

	/**
	 * テスト
	 */
	public function test() {

		//echo $this->Http->getSiteName('http://yahoo.co.jp/');

		echo $this->HttpUtil->expandUrl('http://www.yahoo.co.jp/');

		$this->render('index');
	}


}