<?php
App::uses('FbApiAccessorComponent', 'Controller/Component');
/**
 *
 *
 *
 */
class FbApiAccessorComponentTest extends CakeTestCase  {

	private $fb;
	private $Collection;

	public function setUp() {
		parent::setUp();

		$collection = new ComponentCollection();
		$this->fb   = new FbApiAccessorComponent($collection);
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function getShareCountOfUrls() {
		$urls = array(
				'http://baseballstats2011.jp/archives/20699712.html',
				'http://baseballstats2011.jp/',
				'http://blog.livedoor.jp/domesoccer/'
				);

		$counts = $this->fb->getShareCountOfUrls($urls);

		debug($counts);

		foreach ($counts as $count) {
			$this->assertNotEqual($count, 0);
		}
	}

	/**
	 * 異常系
	 *
	 * @test
	 */
	public function getShareCountOfUrlsInvalidUrls() {
		$urls = array(
				'acss',
				'http://test.aaaaa'
		);

		$counts = $this->fb->getShareCountOfUrls($urls);

		debug($counts);

		foreach ($counts as $count) {
			$this->assertEqual($count, 0);
		}
	}
}

