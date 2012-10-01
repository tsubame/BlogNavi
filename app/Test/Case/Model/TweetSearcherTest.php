<?php

App::uses('TweetSearcher', 'Model');

/**
 *
 *
 * @author hid
 *
 */
class TweetSearcherTest extends CakeTestCase  {

	private $TweetSearcher;

	public function setUp() {
		parent::setUp();
		$this->TweetSearcher = ClassRegistry::init('TweetSearcher');
	}

	/**
	 *
	 */
	public function testExec() {
		//$tSearch = new TweetSearcher();
		$this->TweetSearcher->exec();
	}


}