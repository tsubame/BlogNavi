<?php

App::uses('ArticleGetTweetCountAction', 'Model');

/**
 *
 *
 *
 */
class ArticleGetTweetCountActionTest extends CakeTestCase  {

	private $ArticleGetTweetCountAction;

	public function setUp() {
		parent::setUp();
		$this->ArticleGetTweetCountAction = ClassRegistry::init('ArticleGetTweetCountAction');
	}

	/**
	 *
	 * @test
	 */
	public function execTest() {
		$this->ArticleGetTweetCountAction->exec();
	}


}
/*
class ArticleInsertActionExtend extends ArticleInsertAction {


	public function createApiRequestUrl($url) {
		return parent::createApiRequestUrl($url);
	}

	public function formatUrlForSearch($url) {
		return parent::formatUrlForSearch($url);
	}


}
*/