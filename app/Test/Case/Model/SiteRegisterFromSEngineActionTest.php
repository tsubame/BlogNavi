<?php

App::uses('SiteRegisterFromSEngineAction', 'Model');

/**
 *
 *
 *
 */
class SiteRegisterFromSEngineActionTest extends CakeTestCase  {

	private $action;

	/**
	 *
	 *
	 * @var array
	 */
	public $fixtures = array('site', 'article');

	/**
	 * 初期処理
	 *
	 * @see CakeTestCase::setUp()
	 */
	public function setUp() {
		parent::setUp();

		$this->action = ClassRegistry::init('SiteRegisterFromSEngineAction');
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
	 *
	 *
	 * test
	 */
	public function execTest() {
		$this->action->exec();
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function pickupMultiWordFromArticleTitle() {
		// プライベートメソッドテスト用
		$ref = new ReflectionMethod('SiteRegisterFromSEngineAction', 'pickupMultiWordFromArticleTitle');
		$ref->setAccessible(true);
		$multiWords = $ref->invoke(new SiteRegisterFromSEngineAction());

		debug($multiWords);
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function pickupMultiWords() {
		$keywords = array(
					'本田圭佑',
					'田中',
					'CSKA本田圭佑',
					'田中さん',
					'本田',
					'山口',
					'萌え萌え山口さん',
					'山口',
					'本西'
				);

		$expecteds = array('本田' => 3, '田中' => 2, '山口' => 3);

		// プライベートメソッドテスト用
		$ref = new ReflectionMethod('SiteRegisterFromSEngineAction', 'pickupMultiWords');
		$ref->setAccessible(true);
		$multiWords = $ref->invoke(new SiteRegisterFromSEngineAction(), $keywords);

		//debug($multiWords);

		foreach ($multiWords as $word => $count) {
			$this->assertEqual($multiWords[$word], $expecteds[$word]);
		}
	}

}
