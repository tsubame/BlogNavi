<?php

//App::uses('TwitterAPIAccessor', 'Model');

/**
 *
 *
 * @author hid
 *
 */
class TwitterAPIAccessorTest extends CakeTestCase  {

	private $TwitterAPIAccessor;

	public function setUp() {
		parent::setUp();
		//$this->ArticleInsertAction = ClassRegistry::init('ArticleInsertAction');
		//$this->ArticleInsertAction = new ArticleInsertAction();
		$this->TwitterAPIAccessor = ClassRegistry::init('TwitterAPIAccessorExtend');
		//$this->ArticleInsertAction = new ArticleInsertActionExtend();
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function createApiRequestUrlTest() {
		$url = 'http://yahoo.co.jp/';

		$yesterday = date('Y-m-d', strtotime('-1 day'));

		$reqUrl = $this->TwitterAPIAccessor->createApiRequestUrl($url);

		$this->assertEqual($reqUrl, ArticleInsertAction::SEARCH_API_URL . "?rpp=" .
				ArticleInsertAction::RPP . "&q=yahoo.co.jp/+since:{$yesterday}&include_entities=true");
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function formatUrlForSearchTest() {

		$url = 'http://yahoo.co.jp/';
		$q = $this->TwitterAPIAccessor->formatUrlForSearch($url);
		$this->assertEqual($q, 'yahoo.co.jp/');

		$url = 'http://blog.livedoor.jp/domesoccer/archives/51987811.html';
		$q = $this->TwitterAPIAccessor->formatUrlForSearch($url);
		$this->assertEqual($q, 'blog.livedoor.jp/domesoccer/archives/');

		$url = 'http://blog.livedoor.jp/?q=112';
		$q = $this->TwitterAPIAccessor->formatUrlForSearch($url);
		$this->assertEqual($q, 'blog.livedoor.jp/');


		$url = 'aaa';
		$q = $this->TwitterAPIAccessor->formatUrlForSearch($url);
		$this->assertEqual($q, 'aaa');
	}

	/**
	 * 異常系
	 *
	 * nullを渡す
	 *
	 * @test
	 */
	public function formatUrlForSearchTestNull() {

		$url = null;
		$q = $this->TwitterAPIAccessor->formatUrlForSearch($url);
		$this->assertNull($q);

		$url = '';
		$q = $this->TwitterAPIAccessor->formatUrlForSearch($url);

		$this->assertEqual($q, '');
	}

}

class TwitterAPIAccessorExtend extends TwitterAPIAccessor {


	public function createApiRequestUrl($url) {
		return parent::createApiRequestUrl($url);
	}

	public function formatUrlForSearch($url) {
		return parent::formatUrlForSearch($url);
	}


}