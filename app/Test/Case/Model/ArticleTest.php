<?php

App::uses('Article', 'Model');

/**
 *
 *
 * @author hid
 *
 */
class ArticleTest extends CakeTestCase  {

	private $Article;

	/**
	 * Fixture
	 *
	 * @var array
	 */
	//public $fixtures = array('Article', 'Site', 'Category');

	/**
	 * 初期処理
	 *
	 * @see CakeTestCase::setUp()
	 */
	public function setUp() {
		parent::setUp();
		$this->Article = ClassRegistry::init('Article');
	}

	/**
	 * 終了処理
	 *
	 * @see CakeTestCase::tearDown()
	 */
	public function tearDown() {
		unset($this->Article);

		parent::tearDown();
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function selectTodaysBlogArticles() {
		$articles = $this->Article->selectTodaysBlogArticles();

		//debug($articles);
	}


	/**
	 * 正常系
	 *
	 * @test
	 */
	public function selectTodaysArticlesTest() {
		$articles = $this->Article->selectTodaysArticles(0);

		//debug($articles);
		//$this->assertNotEqual(count($articles), 0);
	}

	/**
	 * 正常系
	 *
	 * test
	 */
	public function insertTest() {

		$article = array(
					'title' => 'タイトル',
					'url' => 'http://url.test'
				);

		$this->Article->save($article);
	}

	/**
	 * 正常系
	 *
	 * test
	 */
	public function saveIfNotExists() {
		$rand = rand(0, 99999);

		$url = 'http://google.com/' . $rand;

		// 1軒目のデータを挿入
		$article = array('url' => $url);
		$res = $this->Article->saveIfNotExists($article);
		$this->assertNotEqual($res, false);

		// 2件目のデータを挿入
		$article2 = array('url' => $url);
		$res2 = $this->Article->saveIfNotExists($article2);
		$this->assertEqual($res2, false);

		// 3件目のデータを挿入
		$article3 = array('url' => $url);
		$res3 = $this->Article->saveIfNotExists($article3);
		$this->assertEqual($res3, false);
	}

	/**
	 * 複数件の記事を挿入できる
	 *
	 * test
	 */
	public function insertMultipleArticles() {
		$insertCount = 30;

		// データを1件挿入
		$fixedUrl = 'http://fixed.' . time();
		$fixedArticle = array(
				'title' => '固定のデータ',
				'url' => $fixedUrl
		);
		$res = $this->Article->save($fixedArticle);

		// ランダムにデータ生成
		$articles = array();
		for ($i = 0; $i < $insertCount; $i++) {
			$article = array(
					'title' => 'ランダムなデータ',
					'url' => 'http://' . time() . '.' . $i
			);

			array_push($articles, $article);
		}
		// 最初に挿入したデータと同じ物を追加
		array_push($articles, $fixedArticle);

		// データの件数ループ
		foreach ($articles as $i => $article) {

			$conditions = array(
							'Article.url' => $article['url']
			);
			$res = $this->Article->hasAny($conditions);
			if ($res == false) {
				$this->Article->create();
				$this->Article->save($article);
				//debug('追加しました' . $article['url']);
			} else {
				//debug('追加できませんでした' . $article['url']);
			}

			if ($i == $insertCount) {
				$this->assertEqual($res, true);
			} else {
				$this->assertEqual($res, false);
			}
		}
	}

	/**
	 * 正常系
	 *
	 * test
	 */
	public function deletePastArticles() {
		 $this->Article->deletePastArticles();
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function selectDeletableArticles() {

		$articles = $this->Article->selectDeletableArticles(1, 1);

		//debug($articles);
	}

	/**
	 * 複数件挿入のパフォーマンスチェック
	 *
	 * @test
	 */
	public function checkPerformance() {
		$ts = time();
		$dataCount = 1000;
		$articles = array();

		// 挿入用データ
		for ($i = 0; $i < 30; $i++) {
			$article = array();
			$article['url'] = 'http://test.test.' . $i;
			$articles[] = $article;

			//$this->Article->saveIfNotExists($article);
		}

		// 挿入用データ
		for ($i = 0; $i < $dataCount; $i++) {
			$article = array();
			$article['url'] = 'http://test.test.' . $i;
			$articles[] = $article;

			//$this->Article->saveIfNotExists($article);
			//$this->Article->save($article);
		}

		$saveArticles = array();
		//
		foreach ($articles as $article) {
			$this->Article->set($article);
			$res = $this->Article->isUnique(array('url'));
			if ($res == true) {
				$saveArticles[] = $article;
			}
		}

		try {
			$this->Article->saveAll($saveArticles);
		} catch(Exception $e) {
			debug($e->getMessage());
		}


		$saveTime = time() - $ts;
		debug("{$dataCount}件の挿入にかかった時間は{$saveTime}秒");

		$results = $this->Article->find('all');

		debug(count($results));
		//debug($results);




		// 一度に同じURLを挿入した場合の対処
		$sql = 'SELECT url, count(url) AS `count` FROM `articles`
				GROUP BY `url` HAVING `count` >= 2 ORDER BY `count` DESC';

		$results = $this->Article->query($sql);


//debug($results);
		foreach ($results as $result) {
			//debug('重複:' . $result['articles']['url']);
			$url = $result['articles']['url'];

			$result = $this->Article->findByUrl($url);
			//debug($result);
			$id = $result['Article']['id'];

			$res = $this->Article->delete($id);
			//debug($res);
		}

		$results = $this->Article->find('all');

		//debug($results);
	}

}