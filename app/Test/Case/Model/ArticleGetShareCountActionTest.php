<?php

App::uses('ArticleGetShareCountAction', 'Model');

/**
 *
 *
 *
 */
class ArticleGetShareCountActionTest extends CakeTestCase  {

	private $action;

	public $fixtures = array('article', 'site');

	public function setUp() {
		parent::setUp();
		$this->action = ClassRegistry::init('ArticleGetShareCountAction');
	}

	public function tearDown() {
		unset($this->action);
		parent::tearDown();
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function exec() {
		$this->action->exec();
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function getTweetCounts() {
		$articles = array(
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
						),
					4 => array(
							'title' => 'ランパード、今年で退団か',
							'url' => 'http://sportsnavi.yahoo.co.jp/soccer/club_wcup/2012/headlines/20121214-00000004-goal-socc.html'
					),
				);

		$minExpecteds = array(3600, 160, 200, 30, 30);

		// クラス名、メソッド名を入れる
		$ref = new ReflectionMethod('ArticleGetShareCountAction', 'getTweetCounts');
		$ref->setAccessible(true);
		// 第2引数でメソッドへの引数を渡す
		$retArticles = $ref->invoke(new ArticleGetShareCountAction(), $articles);

		foreach ($retArticles as $i => $article) {
			$this->assertLessThan($article['tweeted_count'], $minExpecteds[$i]);
		}

		debug($retArticles);
	}

	/**
	 * 異常系
	 * URLが間違っているなど
	 *
	 * @test
	 */
	public function getTweetCountsInvalidUrls() {
		$articles = array(
				0 => array(
						'title' => 'engadget',
						'url' => 'http://japane'
				),
				1 => array(
						'title' => 'ドメサカ板まとめブログ',
						'url' => 'http://blog.livedo'
				),
				2 => array(
						'title' => 'もとにし',
						'url' => 'http://ameblo.jp/nin-s'
				),
				3 => array(
						'title' => '本田がローマ入りもラツィオとの交渉はなし',
						'url' => 'http://www.goal.cB0%E3%81%8C%E3%83%AD%E3%83%BC%E3%83%9E%E5%85%A5%E3%82%8A%E3%82%82%E3%83%A9%E3%83%84%E3%82%A3%E3%82%AA%E3%81%A8%E3%81%AE%E4%BA%A4%E6%B8%89%E3%81%AF%E3%81%AA%E3%81%97'
				),
				4 => array(
						'title' => 'ランパード、今年で退団か',
						'url' => 'h04-goal-socc.html'
				),
		);

		// クラス名、メソッド名を入れる
		$ref = new ReflectionMethod('ArticleGetShareCountAction', 'getTweetCounts');
		$ref->setAccessible(true);
		// 第2引数でメソッドへの引数を渡す
		$retArticles = $ref->invoke(new ArticleGetShareCountAction(), $articles);

		debug($retArticles);

		foreach ($retArticles as $i => $article) {
			$this->assertEqual($article['tweeted_count'], 0);
		}
	}

}