<?php

App::uses('ComponentCollection', 'Controller');
App::uses('CurlMultiComponent', 'Controller/Component');

/**
 * テストクラス
 */
class CurlMultiComponentTestCase extends CakeTestCase {

	private $curlMulti;

	public function setUp(){
		parent::setUp();
		$Collection = new ComponentCollection();
		$this->curlMulti = new CurlMultiComponent($Collection);
	}

	/**
	 * 正常系
	 * ・帰ってきたデータの件数が正しいか
	 * ・帰ってきたデータがすべてfalseではないか
	 *
	 * @test
	 */
	public function getContents() {
		$urls = array(
				'http://bit.ly/OUkl1v',
				'http://bit.ly/W3d3xO',
				'http://bit.ly/PjOJ5C',
				'http://ameblo.jp/nin-shin/',
				'http://blog.livedoor.jp/domesoccer/archives/51987811.html');

		$contents = $this->curlMulti->getContents($urls);
		$this->assertEqual(count($contents), 5);

		foreach ($contents as $content) {
			$this->assertNotEqual($content, false);
		}
	}

	/**
	 * 異常系
	 * 存在しないアドレスを指定
	 *
	 * @test
	 */
	public function getContentsHostNotFound () {
		$urls = array(
				'http://aaaabit.ly/OUkl1v',
				'http://aaa.html');

		$contents = $this->curlMulti->getContents($urls);
		$this->assertEqual(count($contents), 2);

		foreach ($contents as $content) {
			$this->assertEqual($content, false);
		}
	}

	/**
	 * 異常系
	 * http以外のプロトコルを指定
	 *
	 * @test
	 */
	public function getContentsInvalidProtocol () {
		$urls = array(
				'ftp://aaaabit.ly/OUkl1v',
				'ttp://aaa.html');

		$contents = $this->curlMulti->getContents($urls);
		$this->assertEqual(count($contents), 2);

		foreach ($contents as $content) {
			$this->assertEqual($content, false);
		}
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function expandUrls() {

		$urls = array(
				'http://bit.ly/OUkl1v',
				'http://bit.ly/W3d3xO',
				'http://bit.ly/PjOJ5C');

		$longUrls = $this->curlMulti->expandUrls($urls);

		$this->assertEqual($longUrls[0], 'http://jp.uefa.com/news/newsid=1866517.html');
		$this->assertEqual($longUrls[1], 'http://sportsnavi.yahoo.co.jp/baseball/npb/headlines/20120930-00000016-spnavi-base.html');
		$this->assertEqual($longUrls[2], 'http://sportsnavi.yahoo.co.jp/baseball/headlines/20120930-00000019-spnavi-base.html');

		debug($longUrls);
	}


}