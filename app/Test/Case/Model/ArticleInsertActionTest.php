<?php

App::uses('ArticleInsertAction', 'Model');

/**
 *
 *
 * @author hid
 *
 */
class ArticleInsertActionTest extends CakeTestCase  {

	private $ArticleInsertAction;

	public function setUp() {
		parent::setUp();
		//$this->ArticleInsertAction = ClassRegistry::init('ArticleInsertAction');
		$this->ArticleInsertAction = new ArticleInsertAction();
	}

	/**
	 *
	 * @test
	 */
	public function execTest() {
		$this->ArticleInsertAction->exec();
	}




}