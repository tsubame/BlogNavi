<?php

App::uses('FacebookAPIAccessor', 'Model');

/**
 *
 *
 *
 */
class FacebookAPIAccessorTest extends CakeTestCase  {

	private $fb;

	public function setUp() {
		parent::setUp();
		$this->fb = ClassRegistry::init('FacebookAPIAccessorExtend');
	}

	/**
	 * 特定のサイトの「いいね」数を取得する
	 *
	 * @test
	 */
	public function getShareCount() {

		$url = "http://ameblo.jp/nin-shin/";
		$count = $this->fb->getShareCount($url);
		debug($count);

		$this->assertEqual($count, 2);
	}

	/**
	 * 複数のサイトの「いいね」数を取得する
	 *
	 * @test
	 */
	public function getShareCountOfUrls() {

		$urls = array('http://ameblo.jp/nin-shin/', 'http://www.earlbox.sakura.ne.jp/');

		$expecteds = array(2, 3);

		$results = $this->fb->getShareCountOfUrls($urls);
		debug($results);

		$i = 0;
		foreach ($results as $url => $count) {
			$this->assertEqual($count, $expecteds[$i]);
			$i ++;
		}

		debug($results);
	}

}

class FacebookAPIAccessorExtend extends FacebookAPIAccessor {


	public function createApiRequestUrl($url) {
		return parent::createApiRequestUrl($url);
	}


}