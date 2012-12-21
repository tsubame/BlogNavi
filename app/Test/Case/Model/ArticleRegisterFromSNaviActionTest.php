<?php

App::uses('ArticleRegisterFromSNaviAction', 'Model');

/**
 *
 *
 *
 */
class ArticleRegisterFromSNaviActionTest extends CakeTestCase  {

	private $action;

	public $fixtures = array('article', 'site', 'category');

	public function setUp() {
		parent::setUp();
		$this->action = ClassRegistry::init('ArticleRegisterFromSNaviAction');
	}

	public function tearDown() {
		unset($this->action);
		parent::tearDown();
	}


	/**
	 *
	 *
	 * @test
	 */
	public function execTest() {
		$this->action->exec();
	}

	/**
	 * 正常系
	 *
	 * test
	 */
	public function getTodaysArticleUrls() {
		$ref = new ReflectionMethod('ArticleRegisterFromSNaviAction', 'getTodaysArticleUrls');
		$ref->setAccessible(true);
		$urls = $ref->invoke(new ArticleRegisterFromSNaviAction());

		debug($urls);

		foreach($urls as $url) {
			$this->assertNotEqual($url, null);
		}
	}

	/**
	 * 正常系
	 *
	 * test
	 */
	public function getTodaysArticles() {
		// プライベートメソッドテスト用
		$ref = new ReflectionMethod('ArticleRegisterFromSNaviAction', 'getTodaysArticles');
		$ref->setAccessible(true);

		// 第2引数でメソッドへの引数を渡す
		$articles = $ref->invoke(new ArticleRegisterFromSNaviAction());

		foreach($articles as $article) {
			$pubTs = strtotime($article['published']);

			if ((time() - $pubTs) < 86400) {
				$this->assertEqual(true, true);
			} else {
				$this->assertEqual(true, false);
			}
		}
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function getSaveTweetedCount() {
		// プライベートメソッドテスト用
		$ref = new ReflectionMethod('ArticleRegisterFromSNaviAction', 'getSaveTweetedCount');
		$ref->setAccessible(true);

		// 第2引数でメソッドへの引数を渡す
		$minCount = $ref->invoke(new ArticleRegisterFromSNaviAction());

		debug($minCount);
	}

}
