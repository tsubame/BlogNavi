<?php

App::uses('SiteGetLikeCountAction', 'Model/Action');

/**
 *
 *
 *
 */
class SiteGetLikeCountActionTest extends CakeTestCase  {

	/**
	 * SiteGetLikeCountAction
	 *
	 * @var object SiteGetLikeCountAction
	 */
	private $action;

	public function setUp() {
		parent::setUp();
		$this->action = ClassRegistry::init('SiteGetLikeCountAction');
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

class SiteGetLikeCountActionExtend extends SiteGetLikeCountAction {

}