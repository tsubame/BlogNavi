<?php

App::uses('ArticleInsertAction', 'Model');

/**
 *
 *
 *
 */
class ArticleInsertActionTest extends CakeTestCase  {

	private $ArticleInsertAction;

	public function setUp() {
		parent::setUp();
		$this->ArticleInsertAction = ClassRegistry::init('ArticleInsertActionExtend');
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