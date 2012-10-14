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
		$this->Site = ClassRegistry::init('Site');
	}

	/**
	 *
	 * @test
	 */
	public function getSitesOfCategoriesTest() {
		$sites = $this->Site->getSitesOfCategory();

		debug($sites);
	}


}