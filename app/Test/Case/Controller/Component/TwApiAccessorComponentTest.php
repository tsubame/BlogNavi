<?php
App::uses('TwApiAccessorComponent', 'Controller/Component');
/**
 *
 *
 *
 */
class TwApiAccessorComponentTest extends CakeTestCase  {

	private $tw;
	private $Collection;

	public function setUp() {
		parent::setUp();

		$collection = new ComponentCollection();
		$this->tw   = new TwApiAccessorComponent($collection);
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function getTweetCountOfUrls() {
		$urls = array(
				'http://baseballstats2011.jp/archives/20699712.html',
				'http://baseballstats2011.jp/'
				);

		$rtCounts = $this->tw->getTweetCountOfUrls($urls);

		debug($rtCounts);
	}

	/**
	 * 異常系
	 * 不正なURL
	 *
	 * @test
	 */
	public function getTweetCountOfUrlsInvalidUrl() {
		$urls = array(
				'http://bahives/20699712.h',
				'lslslskk'
		);

		$rtCounts = $this->tw->getTweetCountOfUrls($urls);

		// 0が帰ってくればOK
		foreach ($rtCounts as $count) {
			$this->assertEqual($count, 0);
		}
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function getTweetCountOfUrl() {
		$url = 'http://yahoo.co.jp/';

		$rtCount = $this->tw->getTweetCountOfUrl($url);
		$this->assertNotEqual($rtCount, 0);

		$url = 'http://www.goal.com/jp/';

		$rtCount = $this->tw->getTweetCountOfUrl($url);
		$this->assertNotEqual($rtCount, 0);
	}

}

