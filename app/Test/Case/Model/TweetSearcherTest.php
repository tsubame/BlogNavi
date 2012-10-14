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
		//$this->TweetSearcher->exec();
	}

	/**
	 * HTTPアクセスのテスト
	 *
	 * test
	 */
	public function httpTest() {

		$url = 'http://localhost/FootBlogNavi/articles/insert';

		require_once 'HTTP/Request2.php';

		$request = new HTTP_Request2($url, HTTP_Request2::METHOD_GET);
		try {
		    $response = $request->send();
		    if (200 == $response->getStatus()) {
		        debug($response->getBody());
		    } else {
		        echo 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
		             $response->getReasonPhrase();
		    }
		} catch (HTTP_Request2_Exception $e) {
		    debug('Error: ' . $e->getMessage());
		}
	}

	/**
	 * 日付取得のテスト
	 *
	 * @test
	 */
	public function getDateTest() {

		$url = 'http://sportsnavi.yahoo.co.jp/soccer/headlines/20121013-00000012-spnavi-socc.html';
		//$url = 'http://sportsnavi.yahoo.co.jp/soccer/';
		$url = 'http://www.soccer-king.jp/news/japan/national/20121013/76286.html';

		$h = get_headers($url, true);

		debug($h);
		if(isset($h['Last-Modified'])){
			$modified = $h['Last-Modified'];

			debug($modified);
		}
	}

}