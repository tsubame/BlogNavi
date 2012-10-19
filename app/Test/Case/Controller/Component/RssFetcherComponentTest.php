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
	 * デモ
	 *
	 * 正常系
	 *
	 * test
	 */
	public function getFeedTest() {
		$url = 'http://sportsnavi.yahoo.co.jp/';

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

		foreach ($urls as $url) {
			$entries = $this->rssFetcher->getFeed($url);

			//debug($entries);
		}
	}

	/**
	 * デモ
	 *
	 * 正常系
	 *
	 * @test
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

}