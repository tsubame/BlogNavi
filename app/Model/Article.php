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

class Article extends AppModel{

	public $belongsTo = array("Site" =>
			array("className" => "Site",
					"conditions" => "",
					"foreignKey" => "site_id"));

	public $cacheQueries = true;
	//public $actsAs	= array('Cache');

	const LIMIT_COUNT = 20;

	/**
	 * 24時間以内の記事を取得
	 *
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
				'limit' => self::LIMIT_COUNT
				);

		$results = $this->find('all', $options);

		return $results;
	}

	/**
	 * 24時間以内の記事を取得
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
}