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
	 */
	public function getAllCategories() {
		$categories = array();

		$results = $this->find('all');
		// 配列に移し替え
		foreach ($results as $data) {
			$categories[] = $data['Category'];
		}

		return $categories;
	}

	/**
	 * すべてのカテゴリ名を取得
	 *
	 * @return array $categories カテゴリ名の配列（配列の添字は1から）
	 */
	public function getCategoryNames() {
		$categories = array();

		$results = $this->find('all');
		// 配列に移し替え
		foreach ($results as $data) {
			$id = $data['Category']['id'];
			$categories[$id] = $data['Category']['name'];
		}

		return $categories;
	}

}