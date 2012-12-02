<?php

App::uses('SiteRegisterFromSNaviAction', 'Model');
App::uses('SiteRegisterFromSNaviActionExtend', 'Model');

/**
 *
 *
 *
 */
class SiteRegisterFromSNaviActionTest extends CakeTestCase  {

	private $action;

	public function setUp() {
		parent::setUp();
		$this->action = ClassRegistry::init('SiteRegisterFromSNaviActionExtend');
	}

	/**
	 *
	 *
	 * @test
	 */
	public function execTest() {
		$this->action->exec();
	}


}

class SiteRegisterFromSNaviActionExtend extends SiteRegisterFromSNaviAction {


}