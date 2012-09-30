<?php

App::uses('ComponentCollection', 'Controller');
App::uses('HttpParallelComponent', 'Controller/Component');

/**
 *
 */
class HttpParallelComponentTestCase extends CakeTestCase {

	private $httpParallel;

	public function setUp(){
		parent::setUp();
		$Collection = new ComponentCollection();
		$this->httpParallel = new HttpParallelComponent($Collection);
	}

	public function startTest($method) {

	}

	public function endTest($method) {

	}

	/**
	 *
	 * @test
	 */
	public function getDataParallelTest() {

		$urls = array (
					'http://www.soccer-king.jp/news/world/world_other/20120928/73716.html'
					//'http://www.soccer-king.jp/news/world/ita/20120927/73528.html',
					//'http://www.soccer-king.jp/news/world/ger/20120928/73645.html',
					//'http://www.soccer-king.jp/premium/article/72078.html',
					//'http://www.soccer-king.jp/news/world/ger/20120928/73636.html',
					//'http://www.soccer-king.jp/news/world/ger/20120927/73571.html',
					//'http://www.soccer-king.jp/sk_column/article/73544.html',
					//'http://www.footballaustralia.com.au/aleague/results',
					//'http://www.soccer-king.jp/news/japan/20120928/73677.html'
				);

		$results = $this->httpParallel->demo();


		debug ($results);
	}

}