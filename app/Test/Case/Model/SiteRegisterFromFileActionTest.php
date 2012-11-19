<?php

App::uses('SiteRegisterFromFileAction', 'Model');
//App::uses('SiteRegisterAutoActionExtend', 'Model');

/**
 *
 *
 *
 */
class SiteRegisterFromFileActionTest extends CakeTestCase  {

	private $action;

	public function setUp() {
		parent::setUp();
		$this->action = ClassRegistry::init('SiteRegisterFromFileAction');
		$this->action = ClassRegistry::init('SiteRegisterFromFileActionExtend');
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
		$this->action->register(1, SiteRegisterFromFileAction::FILE_NEWS);
		$this->action->register(2, SiteRegisterFromFileAction::FILE_2CH);
		$this->action->register(3, SiteRegisterFromFileAction::FILE_BLOG);
	}

	/**
	 * getFeedUrlAndSiteName() 正常系
	 *
	 * @test
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
	 * @test
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
	 * @test
	 */
	public function cantOpenFile() {

	}

	/**
	 * フィードURLが取得できないとき
	 *
	 * @test
	 */
	public function cantGetFeedUrl() {

	}

	/**
	 * 書式がおかしい行がある
	 *
	 * @test
	 */
	public function invalidLine() {

	}

}


/**
 *
 *
 */
class SiteRegisterFromFileActionExtend extends SiteRegisterFromFileAction  {

	public function splitUrlAndSiteName($text) {
		return parent::splitUrlAndSiteName($text);
	}

	public function getFeedUrlAndSiteName($site) {
		return parent::getFeedUrlAndSiteName($site);
	}

	public function register($catId, $fileName) {
		return parent::register($catId, $fileName);
	}
}
