<?php

App::uses('SiteGetShareCountAction',       'Model/Action');
App::uses('SiteGetShareCountActionExtend', 'Model/Action');
/**
 *
 *
 *
 *
 *
 */
class SiteGetShareCountActionTest extends CakeTestCase  {

	private $action;

	/**
	 *
	 *
	 * @var array
	 */
	public $fixtures = array('site');

	/**
	 * 初期処理
	 *
	 * @see CakeTestCase::setUp()
	 */
	public function setUp() {
		parent::setUp();

		$this->action = ClassRegistry::init('SiteGetShareCountAction');
	}

	/**
	 * 終了処理
	 *
	 * @see CakeTestCase::tearDown()
	 */
	public function tearDown() {
		unset($this->action);

		parent::tearDown();
	}


	/**
	 * 正常系
	 *
	 * test
	 */
	public function execTest() {
		$this->action->exec();
	}

// 未実装
	/**
	 * 正常系
	 *
	 * test
	 */
	public function getTwitterRtCount() {

		$sites = array(
					0 => array(
							'title' => 'engadget',
							'url' => 'http://japanese.engadget.com'
						),
					1 => array(
							'title' => 'ドメサカ板まとめブログ',
							'url' => 'http://blog.livedoor.jp/domesoccer/'
						),
					2 => array(
						'title' => 'もとにし',
						'url' => 'http://ameblo.jp/nin-shin/'
					),
					3 => array(
						'title' => '本田がローマ入りもラツィオとの交渉はなし',
						'url' => 'http://www.goal.com/jp/news/3640/%E3%83%AD%E3%82%B7%E3%82%A2/2012/12/14/3601509/%E6%9C%AC%E7%94%B0%E3%81%8C%E3%83%AD%E3%83%BC%E3%83%9E%E5%85%A5%E3%82%8A%E3%82%82%E3%83%A9%E3%83%84%E3%82%A3%E3%82%AA%E3%81%A8%E3%81%AE%E4%BA%A4%E6%B8%89%E3%81%AF%E3%81%AA%E3%81%97'
						)
				);

		$minExpecteds = array(3600, 160, 200, 30);

		// クラス名、メソッド名を入れる
		$ref = new ReflectionMethod('SiteGetShareCountAction', 'getTwitterRtCount');
		$ref->setAccessible(true);
		// 第2引数でメソッドへの引数を渡す
		$counts = $ref->invoke(new SiteGetShareCountAction(), $sites);

		debug($counts);

		foreach ($counts as $i => $count) {
			$this->assertLessThan($count, $minExpecteds[$i]);
		}
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function getFacebookShareCount() {

		$sites = array(
				0 => array(
						'title' => 'engadget',
						'url' => 'http://japanese.engadget.com'
				),
				1 => array(
						'title' => 'ドメサカ板まとめブログ',
						'url' => 'http://blog.livedoor.jp/domesoccer/'
				),
				2 => array(
						'title' => 'もとにし',
						'url' => 'http://ameblo.jp/nin-shin/'
				),
				3 => array(
						'title' => '本田がローマ入りもラツィオとの交渉はなし',
						'url' => 'http://www.goal.com/jp/news/3640/%E3%83%AD%E3%82%B7%E3%82%A2/2012/12/14/3601509/%E6%9C%AC%E7%94%B0%E3%81%8C%E3%83%AD%E3%83%BC%E3%83%9E%E5%85%A5%E3%82%8A%E3%82%82%E3%83%A9%E3%83%84%E3%82%A3%E3%82%AA%E3%81%A8%E3%81%AE%E4%BA%A4%E6%B8%89%E3%81%AF%E3%81%AA%E3%81%97'
				)
		);

		$minExpecteds = array(0, 13, 2, 57);

		// クラス名、メソッド名を入れる
		$ref = new ReflectionMethod('SiteGetShareCountAction', 'getFacebookShareCount');
		$ref->setAccessible(true);
		// 第2引数でメソッドへの引数を渡す
		$counts = $ref->invoke(new SiteGetShareCountAction(), $sites);

		debug($counts);
/*
		foreach ($counts as $i => $count) {
			$this->assertLessThan($count, $minExpecteds[$i]);
		}
		*/
	}
}
