<?php

//App::uses('Component', 'Controller');
//App::uses('Controller', 'Controller');
App::uses('ComponentCollection', 'Controller');
App::uses('HttpUtilComponent', 'Controller/Component');

/**
 *
 */
class HttpUtilComponentTestCase extends CakeTestCase {

	private $Collection;
	private $httpUtil;

	public function setUp(){
		parent::setUp();
		$this->Collection = new ComponentCollection();
		$this->httpUtil = new HttpUtilComponent($this->Collection);
	}


	/**
	 * urlのコンテンツを取得
	 *
	 * 正常系
	 *
	 * test
	 */
	public function getContents() {
		$url = 'http://sportsnavi.yahoo.co.jp/';

		$html = $this->httpUtil->getContents($url);

		//debug($html);
		$this->assertNotEqual($html, false);
	}

	/**
	 * RSSのURLを取得
	 *
	 * 正常系
	 *
	 * test
	 */
	public function getFeedUrlTest() {

		$urls = array(
				'http://blog.livedoor.jp/domesoccer/',
				'http://sportsnavi.yahoo.co.jp/soccer/',
				'http://www.goal.com/jp/',
				'http://blog.livedoor.jp/nanjstu/',
				'http://www.soccer-king.jp/'
		);

		$expecteds = array(
				'http://blog.livedoor.jp/domesoccer/index.rdf',
				'http://sportsnavi.yahoo.co.jp/rss/soccer.xml',
				'http://www.goal.com/jp/feeds/news?fmt=atom',
				'http://blog.livedoor.jp/nanjstu/index.rdf',
				'http://www.soccer-king.jp/RSS.rdf'
		);

		foreach ($urls as $i => $url) {
			$feedUrl = $this->httpUtil->getFeedUrl($url);
			echo $url . " =>　フィードURL : <a href = '$feedUrl' target = '_blank'>" . $feedUrl . '</a><br />';

			$this->assertNotEqual($feedUrl, false);
			$this->assertEqual($feedUrl, $expecteds[$i]);
		}
	}

	/**
	 * RSSのURLを取得
	 *
	 * 正常系
	 *
	 * @test
	 */
	public function getFeedUrlByCurlTest() {

		$urls = array(
				'http://blog.livedoor.jp/domesoccer/',
				'http://sportsnavi.yahoo.co.jp/soccer/',
				'http://www.goal.com/jp/',
				'http://blog.livedoor.jp/nanjstu/',
				'http://www.soccer-king.jp/'
		);

		$expecteds = array(
				'http://blog.livedoor.jp/domesoccer/index.rdf',
				'http://sportsnavi.yahoo.co.jp/rss/soccer.xml',
				'http://www.goal.com/jp/feeds/news?fmt=atom',
				'http://blog.livedoor.jp/nanjstu/index.rdf',
				'http://www.soccer-king.jp/RSS.rdf'
				);

		foreach ($urls as $i => $url) {
			$feedUrl = $this->httpUtil->getFeedUrlByCurl($url);
			echo $url . " =>　フィードURL : <a href = '$feedUrl' target = '_blank'>" . $feedUrl . '</a><br />';

			$this->assertNotEqual($feedUrl, false);
			$this->assertEqual($feedUrl, $expecteds[$i]);
		}
	}

	/**
	 * RSSのURLを取得
	 *
	 * 正常系
	 * rssとatomのURLが混在するときに指定した方を取得する
	 *
	 * test
	 */
	public function getFeedUrlTest2() {
		//$httpUtil = new HttpUtilComponent($this->Collection);
		//$url = 'http://sportsnavi.yahoo.co.jp/';

		$rssUrl = $this->httpUtil->getRssUrl($url, 'atom');
		//$this->assertEqual($rssUrl, 'http://sportsnavi.yahoo.co.jp/rss/column.xml');

		$rssUrl = $this->httpUtil->getRssUrl($url, 'rss');
	}

	/**
	 * RSSのURLを取得
	 *
	 * 異常系
	 * 取得できない時にfalseを返す
	 *
	 * test
	 */
	public function getFeedUrlTestError() {
		//$httpUtil = new HttpUtilComponent($this->Collection);
		//$url = 'http://sportsnavi.yahoo.co.jp/';

		$rssUrl = $this->httpUtil->getRssUrl($url, 'atom');
		//$this->assertEqual($rssUrl, 'http://sportsnavi.yahoo.co.jp/rss/column.xml');

		$rssUrl = $this->httpUtil->getRssUrl($url, 'rss');
	}

	/**
	 * サイト名を取得
	 *
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
				'http://www.jsgoal.jp/tw/p192d8'
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
		//debug($longUrl);
		$this->assertEqual($longUrl, false);
		// 空文字
		$longUrl = $this->httpUtil->expandUrl('');
		//debug($longUrl);
		$this->assertEqual($longUrl, false);
		// 存在しないURL
		$longUrl = $this->httpUtil->expandUrl('http://aa.com/aaaa.html');
		//debug($longUrl);
		$this->assertEqual($longUrl, 'http://aa.com/aaaa.html');

		$longUrl = $this->httpUtil->expandUrl('http://www.jsgoal.jp/tw/p192d8');
		//debug($longUrl);
		$this->assertEqual($longUrl, 'http://www.jsgoal.jp/photo/00103100/00103128.html');
	}

}