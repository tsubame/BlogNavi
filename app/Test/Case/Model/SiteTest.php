<?php

App::uses('Site', 'Model');

/**
 *
 *
 * @author hid
 *
 */
class SiteTest extends CakeTestCase  {

	private $Site;

	public function setUp() {
		parent::setUp();

		// 削除予定
		$this->Site = new Site();

		$this->Site = ClassRegistry::init('Site');
	}

	/**
	 *
	 * @test
	 */
	public function registerAll() {

		$this->Site->registerAll();
	}

	/**
	 *
	 * @test
	 */
	public function saveIfNotExists() {

		$rand = rand(0, 999999);

		$site = array(
				'name' => 'test',
				'url' => 'http://test/' . $rand);

		$result = $this->Site->saveIfNotExists($site);
		$this->assertNotEqual($result, false);

		$result = $this->Site->saveIfNotExists($site);
		$this->assertEqual($result, false);
	}

	/**
	 *
	 * @test
	 */
	public function getUnregiSites() {

		$sites = $this->Site->getUnregiSites();
		//debug(count($sites));
	}

	/**
	 *
	 * test
	 */
	public function checkDeleted() {
		$site = array('id' => 13);
		$this->Site->checkDeleted($site);
	}


}