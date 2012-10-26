<?php

//App::uses('Component', 'Controller');
//App::uses('Controller', 'Controller');
App::uses('ComponentCollection', 'Controller');
App::uses('RssFetcherComponent', 'Controller/Component');

/**
 *
 */
class RssFetcherComponentTestCase extends CakeTestCase {

	private $Collection;
	private $rssFetcher;

	public function setUp(){
		parent::setUp();
		$this->Collection = new ComponentCollection();
		$this->rssFetcher = new RssFetcherComponent($this->Collection);
	}


	/**
	 *
	 * 正常系
	 *
	 * test
	 */
	public function getFeedTest() {
		$urls = array(
				'http://kyuukaiou.ldblog.jp/',
				'http://ameblo.jp/nin-shin/',
				'http://blog.livedoor.jp/maxell011/',
				'http://2ch11soccer.blog.fc2.com/',
				'http://blog.livedoor.jp/aushio/',
				'http://sportsnavi.yahoo.co.jp/',
				'http://www.plus-blog.sportsnavi.com/takkun/',
				'http://jp.uefa.com/index.html',
				'http://sportiva.shueisha.co.jp/clm/wfootball/',
				'http://japan.cnet.com/'
			);
/*
		$urls = array(
				'http://blog.livedoor.jp/domesoccer/index.rdf',
				'http://sportsnavi.yahoo.co.jp/rss/soccer.xml',
				'http://www.goal.com/jp/feeds/news?fmt=atom',
				'http://blog.livedoor.jp/nanjstu/index.rdf',
				'http://www.soccer-king.jp/RSS.rdf',
				'http://kyuukaiou.ldblog.jp/index.rdf',
				'http://blog.livedoor.jp/maxell011/index.rdf',
				'http://blog.livedoor.jp/aushio/index.rdf',
				'http://feed.japan.cnet.com/rss/index.rdf',
				'http://blog.livedoor.jp/yaruj/index.rdf'
				);
*/
		foreach ($urls as $url) {
			$entries = $this->rssFetcher->getFeed($url);

			debug($entries);
		}
	}

	/**
	 *
	 * 正常系
	 *
	 * test
	 */
	public function getFeedParallelTest() {

		$urls = array(
				'http://blog.livedoor.jp/domesoccer/index.rdf',
				'http://sportsnavi.yahoo.co.jp/rss/soccer.xml',
				'http://www.goal.com/jp/feeds/news?fmt=atom',
				'http://blog.livedoor.jp/nanjstu/index.rdf',
				'http://www.soccer-king.jp/RSS.rdf'
		);

		$urls = array(
				'http://blog.livedoor.jp/domesoccer/index.rdf',
				'http://sportsnavi.yahoo.co.jp/rss/soccer.xml',
				'http://www.goal.com/jp/feeds/news?fmt=atom',
				'http://blog.livedoor.jp/nanjstu/index.rdf',
				'http://www.soccer-king.jp/RSS.rdf',
				'http://kyuukaiou.ldblog.jp/index.rdf',
				'http://blog.livedoor.jp/maxell011/index.rdf',
				'http://blog.livedoor.jp/aushio/index.rdf',
				'http://feed.japan.cnet.com/rss/index.rdf',
				'http://blog.livedoor.jp/yaruj/index.rdf'
				);


/*
		$urls = array(
				'http://blog.livedoor.jp/domesoccer/',
				'http://sportsnavi.yahoo.co.jp/soccer/',
				'http://www.goal.com/jp/',
				'http://blog.livedoor.jp/nanjstu/',
				'http://www.soccer-king.jp/'
		);
*/
		$parsedFeeds = $this->rssFetcher->getFeedParallel($urls);

		//debug($parsedFeeds);

	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function getFeedUrlFromSiteUrlTest() {

		$urls = array(
				'http://jp.uefa.com/index.html',
				'http://sportiva.shueisha.co.jp/clm/wfootball/',
				'http://japan.cnet.com/',
				'http://www.goal.com/jp/'
		);

		$expecteds = array(
				false,
				'http://sportiva.shueisha.co.jp/clm/wfootball/rss.xml',
				'http://feed.japan.cnet.com/rss/index.rdf',
				'http://www.goal.com/jp/feeds/news?fmt=atom'
		);

		foreach ($urls as $i => $url) {
			$feedUrl = $this->rssFetcher->getFeedUrlFromSiteUrl($url);

			$this->assertEqual($feedUrl, $expecteds[$i]);
		}
	}


	/**
	 * 正常系
	 *
	 * @test
	 */
	public function getSiteNameTest() {

		$urls = array(
				'http://jp.uefa.com/index.html',
				'http://sportiva.shueisha.co.jp/clm/wfootball/',
				'http://japan.cnet.com/',
				'http://kapusoku.blog.fc2.com/'
		);


		$expecteds = array(
				false,
				'集英社のスポーツ総合雑誌 スポルティーバ 公式サイト web Sportiva｜World Football',
				'CNET Japan 最新情報　総合',
				'カプ速（広島東洋カープまとめブログ）'
		);

		foreach ($urls as $i => $url) {
			$siteName = $this->rssFetcher->getSiteName($url);

			//debug($siteName);
			$this->assertEqual($siteName, $expecteds[$i]);
		}
	}
}