<?php

App::uses('ComponentCollection', 'Controller');
App::uses('CurlDemoComponent', 'Controller/Component');

/**
 *
 */
class CurlDemoComponentTestCase extends CakeTestCase {

	private $curlDemo;

	public function setUp(){
		parent::setUp();
		$Collection = new ComponentCollection();
		$this->curlDemo = new CurlDemoComponent($Collection);
	}

	/**
	 *
	 * test
	 */
	public function demoTest() {
		$this->curlDemo->demo();
	}

	/**
	 *
	 * test
	 */
	public function multiDemoTest() {
		$this->curlDemo->multiDemo();
	}

	/**
	 *
	 * @test
	 */
	public function expandUrlsTest() {

		$urls = array(
				'http://bit.ly/OUkl1v',
				'http://bit.ly/W3d3xO',
				'http://bit.ly/PjOJ5C');

		//$h = get_headers('http://bit.ly/OUkl1v', true);
		//debug($h);

		//$h = get_headers('http://news.google.com/news/url?sa=t&fd=R&usg=AFQjCNHTNpYrOXDoNoatXD8W6TuAlJqibg&url=http://jp.uefa.com/news/newsid%3D1866517.html&utm_source=API&utm_medium=twitter', true);
		//debug($h);

		$longUrls = $this->curlDemo->expandUrls($urls);

		debug($longUrls);
	}


}