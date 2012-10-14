<?php
/**
 * モデルクラス articlesテーブル
 *
 *
 * テーブル構成
 *
 * id			 integer
 * title		 varchar
 * url			 varchar
 * site_id		 integer
 * tweeted_count integer
 * disable 		 boolean  true 表示しない記事（ニュース以外のURLなど）
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

		$yesterday = date('Y-m-d', strtotime('-1 day'));

		if ( !is_null($categoryId)) {
			$conditions = array('Article.created >' => $yesterday, 'Site.category_id' => $categoryId);
		} else {
			$conditions = array('Article.created >' => $yesterday);
		}

		$options = array(
				'conditions' => $conditions,
				'order' => 'Article.tweeted_count DESC',
				'limit' => self::LIMIT_COUNT
				);

		$results = $this->find('all', $options);

		return $results;
	}

}