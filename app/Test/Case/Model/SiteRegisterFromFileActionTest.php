<?php

App::uses('SiteRegisterFromFileAction', 'Model');

/**
 *
 *
 *
 */
class SiteRegisterFromFileActionTest extends CakeTestCase  {

	private $action;

	public $fixtures = array('article', 'site', 'category');

	public function setUp() {
		parent::setUp();
		$this->action = ClassRegistry::init('SiteRegisterFromFileAction');
	}

	public function tearDown() {
		unset($this->action);
		parent::tearDown();
	}


	/**
	 *
	 * test
	 */
	public function execTest() {
		$this->action->exec();
	}

	/**
	 * register() 正常系
	 *
	 * @test
	 */
	public function register() {
		// ファイル名を定数から取得
		$fileNames = Configure::read('Site.fileNames');

		// プライベートメソッドテスト用
		$ref = new ReflectionMethod('SiteRegisterFromFileAction', 'register');
		$ref->setAccessible(true);

		// 各ファイルから読み込んで登録
		foreach ($fileNames as $i => $fileName) {
			//$this->action->register($i, $fileName);

			// 第2引数でメソッドへの引数を渡す
			$counts = $ref->invoke(new SiteRegisterFromFileAction(), $i, $fileName);
		}
	}

	/**
	 * getFeedUrlAndSiteName() 正常系
	 *
	 * test
	 */
	public function getFeedUrlAndSiteName() {

		$text = "http://www.plus-blog.sportsnavi.com/centric/eusoccer
			http://www.plus-blog.sportsnavi.com/centric/soccer_wcup
			http://www.plus-blog.sportsnavi.com/centric/uefacl";

		$sites = $this->action->splitUrlAndSiteName($text);

		$expectedNames = array('欧州サッカー', 'サッカーワールドカップ', '欧州CL/EL');

		foreach ($sites as $i => $site) {
			$site = $this->action->getFeedUrlAndSiteName($site);

			debug($site);
			$this->assertEqual($site['name'], $expectedNames[$i]);
		}
	}

	/**
	 * splitUrlAndSiteName() 正常系
	 *
	 * test
	 */
	public function splitUrlAndSiteName() {
		$text = "http://www.plus-blog.sportsnavi.com/centric/eusoccer
http://www.plus-blog.sportsnavi.com/centric/soccer_wcup
http://www.plus-blog.sportsnavi.com/centric/uefacl";

		$expectedNames = array(null, null, null);

		$expectedUrls = array("http://www.plus-blog.sportsnavi.com/centric/eusoccer",
								"http://www.plus-blog.sportsnavi.com/centric/soccer_wcup",
								"http://www.plus-blog.sportsnavi.com/centric/uefacl");

		$sites = $this->action->splitUrlAndSiteName($text);

		debug($sites);

		foreach ($sites as $i => $site) {
			$this->assertEqual($site['name'], $expectedNames[$i]);
			$this->assertEqual($site['url'], $expectedUrls[$i]);
		}
	}

	/**
	 * ファイルが開けないとき
	 *
	 * test
	 */
	public function cantOpenFile() {

	}

	/**
	 * フィードURLが取得できないとき
	 *
	 * test
	 */
	public function cantGetFeedUrl() {

	}

	/**
	 * 書式がおかしい行がある
	 *
	 * test
	 */
	public function invalidLine() {

	}

}

