<?php

App::uses('ArticleRegisterAction', 'Model');

/**
 *
 *
 *
 */
class ArticleRegisterActionTest extends CakeTestCase  {

	private $action;

	public function setUp() {
		parent::setUp();
		$this->action = ClassRegistry::init('ArticleRegisterActionExtend');
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function exec() {
		$this->action->exec();
	}


}

class ArticleRegisterActionExtend extends ArticleRegisterAction {


}