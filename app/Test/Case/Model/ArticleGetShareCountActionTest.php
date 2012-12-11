<?php

App::uses('ArticleGetShareCountAction', 'Model');

/**
 *
 *
 *
 */
class ArticleGetShareCountActionTest extends CakeTestCase  {

	private $action;

	public function setUp() {
		parent::setUp();
		$this->action = ClassRegistry::init('ArticleGetShareCountAction');
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