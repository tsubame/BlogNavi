<?php

App::uses('SiteGetShareCountAction',       'Model/Action');
App::uses('SiteGetShareCountActionExtend', 'Model/Action');
/**
 *
 *
 *
 *
 *
 */
class SiteGetShareCountActionTest extends CakeTestCase  {

	private $action;

	public function setUp() {
		parent::setUp();
		$this->action = ClassRegistry::init('SiteGetShareCountAction');
		//$this->action = ClassRegistry::init('SiteGetShareCountActionExtend');
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function execTest() {
		$this->action->exec();
	}
}

/*
class SiteGetShareCountActionExtend extends SiteGetShareCountAction  {

	public function getFacebookShareCount($sites) {
		parent::getFacebookShareCount($sites);
	}

}
*/