<?php

/**
 * モデルクラス sitesテーブル
 *
 * ・sitesテーブル
 * id			integer primary
 * name			varchar
 * url			varchar unique
 * rss_url		varchar
 * category_id	integer
 * disable		boolean
 * created		datetime
 * modified		datetime
 *
 */
App::uses('AppModel', 'Model');

class Site extends AppModel{



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


}