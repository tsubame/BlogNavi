<?php
/**
 * モデルクラス articlesテーブル
 *
 *
 * テーブル構成
 *
 * id			 integer
 * title		 varchar
 * url			 mediumtext
 * description	 mediumtext
 * site_id		 integer
 * tweeted_count integer
 * disable 		 boolean  true 表示しない記事（ニュース以外のURLなど）
 * published 	 datetime
 * created  	 datetime
 * modified 	 datetime
 *
 */
App::uses('AppModel', 'Model');
App::uses('Category', 'Model');

class Article extends AppModel{

	/**
	 * sitesテーブルとの関係
	 *
	 * @var array
	 */
	public $belongsTo = array("Site" =>
			array("className" => "Site",
					"conditions" => "",
					"foreignKey" => "site_id"));

	/**
	 * キャッシュの有効化
	 *
	 * @var bool
	 */
	public $cacheQueries = true;

// 外に出すべき
	/**
	 * 表示する件数
	 *
	 * @var unknown_type
	 */
	const SHOW_COUNT = 10;

	/**
	 * 記事を自動で削除する際に使用。
	 * 1日前の記事から削除する。
	 *
	 * @var int
	 */
	const DEL_DAYS_AGO_START = 1;

	/**
	 * 同上
	 *
	 * @var int
	 */
	const DEL_DAYS = 50;
//

	/**
	 * 24時間以内の記事をツイート数の多い順に取得
	 *
	 * @param  int   $categoryId nullならすべて
	 * @return array $results
	 */
	public function selectTodaysArticles($categoryId = null) {

		$yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));

		if ( !is_null($categoryId)) {
			$conditions = array('Article.published >' => $yesterday, 'Site.category_id' => $categoryId);
		} else {
			$conditions = array('Article.published >' => $yesterday);
		}

		$options = array(
				'conditions' => $conditions,
				'order' => 'Article.tweeted_count DESC',
				'limit' => self::SHOW_COUNT
				);

		$results = $this->find('all', $options);

		return $results;
	}

	/**
	 * 24時間以内の記事をすべて取得
	 *
	 * @return array $results
	 */
	public function selectTodaysAllArticles($categoryId = null) {

		$yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));

		if ( !is_null($categoryId)) {
			$conditions = array('Article.published >' => $yesterday, 'Site.category_id' => $categoryId);
		} else {
			$conditions = array('Article.published >' => $yesterday);
		}

		$options = array(
				'conditions' => $conditions
		);

		$results = $this->find('all', $options);

		$articles = array();
				// 配列に移し替え
		foreach ($results as $data) {
			array_push($articles, $data['Article']);
		}

		return $articles;
	}

//ツイート数が0件の記事も削除すべき？
	/**
	 * 過去数日分の記事を一度に削除する
	 *
	 * 日付、カテゴリごとにツイート数が少ないサイトを削除
	 *
	 */
	public function deletePastArticles() {

		// カテゴリを取得
		$category = ClassRegistry::init('Category');
		$categories = $category->getAllCategories();

		$deleteArticles = array();
		// 削除する日付分ループ
		for($dayAgo = self::DEL_DAYS_AGO_START; $dayAgo < self::DEL_DAYS; $dayAgo++) {
			// カテゴリの件数ループ
			foreach ($categories as $category) {
				$catId = $category['id'];
				$articles = $this->selectDeletableArticles($dayAgo, $catId);

				$deleteArticles = array_merge($deleteArticles, $articles);
			}
		}

		// 削除
		foreach ($deleteArticles as $article) {
			$this->delete($article['id']);
		}

		$delCount = count($deleteArticles);
		// ロギング
		CakeLog::info("{$delCount}件の記事を削除しました。");
	}

	/**
	 * 削除すべき記事を取得
	 *
	 * 日付、カテゴリごとにツイート数が少ないサイトを取得
	 *
	 * @param  int   $dayAgo    何日前の記事を取得するか 0で今日
	 * @param  int   $categoryId カテゴリ番号
	 * @return array $articles
	 */
	public function selectDeletableArticles($dayAgo = 1, $categoryId = 1) {

		if ($dayAgo == 0) {
			$dayAgoMinus = '+0';
		} else {
			$dayAgoMinus = (string)($dayAgo * -1);
		}

		$dateFrom = date('Y-m-d 0:00:00', strtotime($dayAgoMinus . ' day'));
		$dateTo   = date('Y-m-d 23:59:59', strtotime($dayAgoMinus . ' day'));

		$conditions = array('Article.published >' => $dateFrom, 'Article.published <' => $dateTo, 'Site.category_id' => $categoryId);

		$options = array(
				'conditions' => $conditions,
				'order' => 'Article.tweeted_count DESC'
				);

		$allResults = $this->find('all', $options);

		// 先頭から一定の件数を省く
		$deleteResults = array_slice($allResults, self::SHOW_COUNT, 9999);

		$articles = array();

		// 配列に移し替え
		foreach ($deleteResults as $data) {
			array_push($articles, $data['Article']);
		}

		return $articles;
	}

	/**
	 * テスト出力用
	 *
	 * カテゴリごと、日付ごとに記事を出力
	 */
	public function output() {

		for($dayAgo = 1; $dayAgo < 30; $dayAgo++) {

			debug("$dayAgo 日前");

			// カテゴリの件数ループ
			for ($i = 1; $i <= 5; $i++) {
				$dayAgoMinus = (string)($dayAgo * -1);

				$dateFrom = date('Y-m-d 0:00:00',  strtotime($dayAgoMinus . ' day'));
				$dateTo   = date('Y-m-d 23:59:59', strtotime($dayAgoMinus . ' day'));

				$conditions = array('Article.published >' => $dateFrom, 'Article.published <' => $dateTo, 'Site.category_id' => $i);

				$options = array(
						'conditions' => $conditions,
						'order' => 'Article.tweeted_count DESC'
				);

				$results = $this->find('all', $options);

				$count = count($results);

				debug("$count 件");
			}
		}
			// 日付の件数ループ
	}
}