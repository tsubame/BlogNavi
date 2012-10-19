<?php

App::uses('ArticleInsertByTwAction', 'Model');

/**
 *
 *
 * @author hid
 *
 */
class ArticleInsertActionByTwTest extends CakeTestCase  {

	private $ArticleInsertByTwAction;

	public function setUp() {
		parent::setUp();
		//$this->ArticleInsertAction = ClassRegistry::init('ArticleInsertAction');
		//$this->ArticleInsertAction = new ArticleInsertAction();
		$this->ArticleInsertByTwAction = ClassRegistry::init('ArticleInsertByTwActionExtend');
		//$this->ArticleInsertAction = new ArticleInsertActionExtend();
	}

	/**
	 *
	 * @test
	 */
	public function execTest() {
		$this->ArticleInsertByTwAction->exec();
	}


}

class ArticleInsertByTwActionExtend extends ArticleInsertByTwAction {


	public function createApiRequestUrl($url) {
		return parent::createApiRequestUrl($url);
	}

	public function formatUrlForSearch($url) {
		return parent::formatUrlForSearch($url);
	}


}