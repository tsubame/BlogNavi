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
	 * test
	 */
	public function execTest() {
		$this->ArticleInsertAction->exec();
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