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
	 */
	public function selectTodaysArticles() {

		$yesterday = date('Y-m-d', strtotime('-1 day'));

		$options = array(
				'conditions' => array(
					'Article.created >' => $yesterday
						),
				'order' => 'tweeted_count DESC',
				'limit' => self::LIMIT_COUNT
				);

		$results = $this->find('all', $options);

//debug($results);

return $results;
	}

}