<?php

App::uses('SiteRegisterFromArticleAction', 'Model');
App::uses('SiteRegisterFromArticleActionExtend', 'Model');

/**
 *
 *
 *
 */
class SiteRegisterFromArticleActionTest extends CakeTestCase  {

	private $action;

	public function setUp() {
		parent::setUp();
		$this->action = ClassRegistry::init('SiteRegisterFromArticleActionExtend');
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

class SiteRegisterFromArticleActionExtend extends SiteRegisterFromArticleAction {


}