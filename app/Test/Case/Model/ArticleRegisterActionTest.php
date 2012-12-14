<?php

App::uses('ArticleRegisterAction', 'Model');

/**
 *
 *
 *
 */
class ArticleRegisterActionTest extends CakeTestCase  {

	private $action;

	/**
	 * fixture
	 *
	 * @var array
	 */
	public $fixtures = array('article', 'site');

	/**
	 * 初期処理
	 *
	 * @see CakeTestCase::setUp()
	 */
	public function setUp() {
		parent::setUp();

		$this->action = ClassRegistry::init('ArticleRegisterAction');
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
	public function exec() {
		$this->action->exec();
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function createFeedUrlsArray() {
		$sites = array(
					1 => array(
							'feed_url' => 'http://feed.xml'
						),
					2 => array(
							'url' => 'http://url.com'
						)
				);

		$expecteds = array(
					'http://feed.xml',
					'http://url.com'
				);
		// プライベートメソッドテスト用
		$ref = new ReflectionMethod('ArticleRegisterAction', 'createFeedUrlsArray');
		$ref->setAccessible(true);

		$feedUrls = $ref->invoke(new ArticleRegisterAction(), $sites);

		foreach ($feedUrls as $i => $feedUrl) {
			$this->assertEqual($feedUrl, $expecteds[$i]);
		}
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function saveArticle() {
		// プライベートメソッドテスト用
		$ref = new ReflectionMethod('ArticleRegisterAction', 'saveArticle');
		$ref->setAccessible(true);

		$yesDate = date('Y/m/d H:i:s', time() - 86401);

		$article = array(
				'title' => 'test',
				'url' => 'http://test.test',
				'published' => $yesDate
				);

		$result = $ref->invoke(new ArticleRegisterAction(), $article);
		$this->assertEqual($result, false);

		$yesDate = date('Y/m/d H:i:s', time() - 85000);

		$article = array(
				'title' => 'test',
				'url' => 'http://test.test',
				'published' => $yesDate
		);

		$result = $ref->invoke(new ArticleRegisterAction(), $article);
		$this->assertEqual($result, true);
	}


	/**
	 * プライベートメソッドのテスト方法
	 */
	public function demo() {
		// クラス名、メソッド名を入れる
		$ref = new ReflectionMethod('ArticleRegisterAction', 'saveArticle');
		$ref->setAccessible(true);
		// 第2引数でメソッドへの引数を渡す
		$result = $ref->invoke(new ArticleRegisterAction(), $article);
	}

}
