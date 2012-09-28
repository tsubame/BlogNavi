<?php

//App::uses('Component', 'Controller');
//App::uses('Controller', 'Controller');
App::uses('ComponentCollection', 'Controller');
App::uses('HttpUtilComponent', 'Controller/Component');

/**
 *
 */
class HttpUtilComponentTestCase extends CakeTestCase {

	public function setUp(){
		parent::setUp();
		$Collection = new ComponentCollection();
		$this->httpUtil = new HttpUtilComponent($Collection);
	}

	/**
	 * 正常系
	 *
	 */
	public function getSiteNameTest() {

		//echo 'テスト開始';

		$http = new HttpUtilComponent();

		$urls = array(
				'http://sportsnavi.yahoo.co.jp/soccer/',
				'http://php.net/manual/ja/function.preg-match.php',
				'http://blog.milds.net/2011/12/cakephp20.html',
				'http://yakinikunotare.boo.jp/orebase2/cakephp/test_of_model',
				'http://book.cakephp.org/2.0/ja/controllers/components.html',
				'http://www.goal.com/jp/',
				'http://www.goal.com/',
				'http://www.soccer-king.jp/',
				'http://jp.uefa.com/index.html'
		);

		foreach ($urls as $url) {
			$siteName = $http->getSiteName($url);
			debug($url . ' : ' . $siteName);
			$this->assertNotEqual($siteName, null);
		}
	}

	/**
	 * 正常系
	 *
	 * test
	 */
	public function getFeedUrlTest() {
		$http = new HttpUtilComponent();

		$urls = array(
					'http://sportsnavi.yahoo.co.jp/soccer/',
					'http://www.goal.com/jp/',
					'http://www.goal.com/en/',
					'http://www.soccer-king.jp/',
					'http://jp.uefa.com/index.html'
				);

		foreach ($urls as $url) {
			$feedUrl = $http->getFeedUrl($url);

			debug($url . ' : ' . $feedUrl);
			$this->assertNotNull($feedUrl);
		}
	}

	/**
	 * expandUrl() 正常系
	 *
	 * test
	 */
	public function expandUrlTest() {

		$urls = array(
				'http://ow.ly/e1vGw',
				'http://t.co/8s3Pz9Ze',
				'http://eag.ly/y/cgPUs',
				'http://bit.ly/P8TXBe',
				'http://dlvr.it/2DNVlW',
				'http://www.plus-blog.sportsnavi.com/yakyuu_manga/article/174',
				'http://tinyurl.com/8ktesgz',
				'http://google.co.jp/'
		);

		//$this->httpUtil->expandUrl('');

		foreach ($urls as $url) {
			$longUrl = $this->httpUtil->expandUrl($url);
			debug($longUrl);
			$this->assertNotEqual($longUrl, false);
		}
	}

	/**
	 * expandUrl() 正常系
	 *
	 * @test
	 */
	public function expandUrlTestNull() {
		// null
		$longUrl = $this->httpUtil->expandUrl(null);
		debug($longUrl);
		$this->assertEqual($longUrl, false);
		// 空文字
		$longUrl = $this->httpUtil->expandUrl('');
		debug($longUrl);
		$this->assertEqual($longUrl, false);
		// 存在しないURL
		$longUrl = $this->httpUtil->expandUrl('http://aa.com/aaaa.html');
		debug($longUrl);
		$this->assertEqual($longUrl, 'http://aa.com/aaaa.html');

		$longUrl = $this->httpUtil->expandUrl('aaaa.html');
		debug($longUrl);
		$this->assertEqual($longUrl, false);
	}

}