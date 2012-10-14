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

	public function setUp() {
		parent::setUp();
		$this->Article = ClassRegistry::init('Article');
		//$this->Article = new Article();
	}

	/**
	 *
	 * @test
	 */
	public function selectTodaysArticlesTest() {
		$this->Article->selectTodaysArticles();
	}

	/**
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
	 *
	 * @test
	 */
	public function 同じURLの記事は挿入できない() {

		$url = 'http://test5';
		$options = array(
				'conditions' => array(
						'Article.url' => $url
			)
		);

		$data = $this->Article->find('first', $options);

		if ($data == false) {
			$article = array(
					'title' => 'タイトル',
					'url' => $url
			);
			$res = $this->Article->save($article);
		}


		$article = array(
				'title' => 'タイトル',
				'url' => 'http://test'
		);
	}

	/**
	 *
	 * @test
	 */
	public function 同じURLの記事は() {

		$urls = array(
				'http://www.goal.com/jp/news/126/%E3%83%95%E3%83%A9%E3%83%B3%E3%82%B9/2012/10/14/3448173/%E3%83%99%E3%83%B3%E3%82%BC%E3%83%9E%E6%97%A5%E6%9C%AC%E3%81%AB%E8%B2%A0%E3%81%91%E3%81%9F%E3%81%AE%E3%81%AF%E5%AB%8C%E3%81%A0%E3%81%A3%E3%81%9F%E3%81%8C',
				'http://www.goal.com/jp/news/1867/%E3%82%A4%E3%82%BF%E3%83%AA%E3%82%A2/2012/10/14/3448331/%E3%82%A4%E3%83%96%E3%83%A9%E4%BB%A3%E7%90%86%E4%BA%BA%E3%83%90%E3%83%AD%E3%83%86%E3%83%83%E3%83%AA%E3%83%90%E3%83%AB%E3%82%B5%E3%81%B8%E3%81%AE%E7%99%BA%E8%A8%80%E3%82%92%E5%90%A6%E5%AE%9A'
			);

		foreach ($urls as $url) {
			// 同じURLのデータが存在するか調べる
			$result = $this->Article->hasAny(
					array('url' => $url)
			);

			$article = array(
					'title' => 'タイトル',
					'url' => $url
			);

			// なければ追加
			if ($result == false) {
				$this->Article->create();
				$this->Article->save($article);
				debug('追加しました' . $article['url']);
			}
		}
	}

	/**
	 *
	 * @test
	 */
	public function test複数件の記事を挿入できる() {
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
				debug('追加できませんでした' . $article['url']);
			}

			if ($i == $insertCount) {
				$this->assertEqual($res, true);
			} else {
				$this->assertEqual($res, false);
			}
		}
	}

}