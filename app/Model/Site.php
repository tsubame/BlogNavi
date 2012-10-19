<?php

/**
 * モデルクラス sitesテーブル
 *
 * ・sitesテーブル
 * id			integer primary
 * name			varchar
 * url			varchar unique
 * feed_url		varchar
 * category_id	integer
 * disable		boolean
 * created		datetime
 * modified		datetime
 *
 */
App::uses('AppModel', 'Model');

class Site extends AppModel{

	public $belongsTo = array("Category" =>
			array("className" => "Category",
					"conditions" => "",
					"foreignKey" => "category_id"));

	public $cacheQueries = true;

	// モデルに必要なプロパティ、メソッドは？



	/**
	 * すべてのサイトを取得
	 *
	 * @return array $sites サイトの配列
	 *						$sites[0] ('name' = > 'サイト名')
	 */
	public function getAllSites() {
		$sites = array();

		$results = $this->find('all');
		// 配列に移し替え
		foreach ($results as $data) {
			array_push($sites, $data['Site']);
		}

		return $sites;
	}

	/**
	 * 特定のカテゴリのサイトを取得
	 *
	 * @param  int   $categoryId
	 * @return array $sites サイトの配列
	 *						$sites[0] ('name' = > 'サイト名')
	 */
	public function getSitesOfCategory($categoryId = 1) {
		$sites = array();

		$conditions = array('Site.category_id' => $categoryId);

		$options = array(
				'conditions' => $conditions,
				'order' => 'Site.id ASC'
		);

		$results = $this->find('all', $options);
		// 配列に移し替え
		foreach ($results as $data) {
			array_push($sites, $data['Site']);
		}

		return $sites;
	}

}