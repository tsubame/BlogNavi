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
 * is_deleted 	 boolean  true 表示しない記事（ニュース以外のURLなど）
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


	/**
	 * 24時間以内の記事をツイート数の多い順に取得
	 *
	 * $categoryIdがnullならすべてのカテゴリの記事を取得する
	 *
	 * @param  int   $categoryId カテゴリーID、nullはすべて
	 * @return array $results
	 */
	public function selectTodaysArticles($categoryId = null, $getCount = null) {
		// 表示件数の初期値
		if (is_null($getCount)) {
			$getCount = Configure::read('Article.showCount');
		}
		// 日付の文字列を作成
		$yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));
		$tomorrow  = date('Y-m-d H:i:s', strtotime('+1 day'));

		if ( !is_null($categoryId)) {
			$conditions = array('Article.published >' => $yesterday, 'Article.published <' => $tomorrow, 'Site.category_id' => $categoryId);
		} else {
			$conditions = array('Article.published >' => $yesterday, 'Article.published <' => $tomorrow);
		}

		$options = array(
				'conditions' => $conditions,
				'order' => 'Article.tweeted_count DESC',
				'limit' => $getCount
				);

		$results = $this->find('all', $options);

		return $results;
	}

	/**
	 * ブログカテゴリの24時間以内の記事をツイート数の多い順に取得
	 *
	 * @param  int   $categoryId nullならすべて
	 * @return array $results
	 */
	public function selectTodaysBlogArticles() {

		$yesterday = date('Y-m-d H:i:s', strtotime('-1 day'));
		$conditions = array('Article.published >' => $yesterday, 'Site.category_id' => Configure::read('Category.blogId'));

		$options = array(
				'conditions' => $conditions,
				'order' => 'Article.tweeted_count DESC',
				'limit' => Configure::read('Article.showCount')
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
		for($dayAgo = Configure::read('Article.deletePastDayFrom');
				$dayAgo < Configure::read('Article.deletePastTo'); $dayAgo++) {
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

		$dateFrom = date('Y-m-d 0:00:00',  strtotime($dayAgoMinus . ' day'));
		$dateTo   = date('Y-m-d 23:59:59', strtotime($dayAgoMinus . ' day'));

		$conditions = array('Article.published >' => $dateFrom, 'Article.published <' => $dateTo, 'Site.category_id' => $categoryId);

		$options = array(
				'conditions' => $conditions,
				'order' => 'Article.tweeted_count DESC'
				);

		$allResults = $this->find('all', $options);

		// 先頭から一定の件数を省く
		$deleteResults = array_slice($allResults, Configure::read('Article.showCount'), 9999);

		$articles = array();

		// 配列に移し替え
		foreach ($deleteResults as $data) {
			array_push($articles, $data['Article']);
		}

		return $articles;
	}

	/**
	 * 同じURLのデータが存在していなければデータ挿入
	 *
	 * @param  array $article
	 * @return int | bool レコードのID 挿入できなければfalse
	 */
	public function saveIfNotExists($article) {

		// データ削除
		$conditions = array('Article.url' => $article['url']);

		$options = array(
				'conditions' => $conditions
		);
		$results = $this->find('all', $options);

		$count = $this->getNumRows();

		if (0 < $count) {
			return false;
		}

		// なければ追加
		$this->create($article);
		if($this->save($article)) {
			return $this->getInsertID();
		} else {
			return false;
		}
	}


}