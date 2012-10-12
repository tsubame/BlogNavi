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
		//$this->ArticleInsertAction = new ArticleInsertAction();
		$this->ArticleInsertAction = ClassRegistry::init('ArticleInsertActionExtend');
		//$this->ArticleInsertAction = new ArticleInsertActionExtend();
	}

	/**
	 *
	 * @test
	 */
	public function execTest() {
		$this->ArticleInsertAction->exec();
	}


}

class ArticleInsertActionExtend extends ArticleInsertAction {


	public function createApiRequestUrl($url) {
		return parent::createApiRequestUrl($url);
	}

	public function formatUrlForSearch($url) {
		return parent::formatUrlForSearch($url);
	}


}