<?php
/**
 * モデルクラス categoriesテーブル
 *
 *
 * テーブル構成
 *
 * id		integer
 * name		 varchar
 *
 */
App::uses('AppModel', 'Model');

class Category extends AppModel{

	/**
	 * すべてのカテゴリを取得
	 *
	 * @return array $categories カテゴリの配列
	 *						$categories[0] ('name' = > 'カテゴリ名')
	 */
	public function getAllCategories() {
		$categories = array();

		$results = $this->find('all');
		// 配列に移し替え
		foreach ($results as $data) {
			array_push($categories, $data['Category']);
		}

		return $categories;
	}

}