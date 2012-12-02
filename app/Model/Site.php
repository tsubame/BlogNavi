<?php
App::uses('AppModel', 'Model');

/**
 * モデルクラス sitesテーブル
 *
 * sitesテーブルの構成
 *
 * id			   int primary
 * name			   varchar
 * url			   varchar
 * feed_url		   varchar
 * category_id	   int
 * like_count	   int
 * registered_from varchar
 * is_categorized  boolean
 * is_available	   boolean
 * created		   datetime
 * modified		   datetime
 */
class Site extends AppModel{

	/**
	 * 他テーブルとの関連性
	 *
	 * @var array
	 */
	public $belongsTo = array(
		"Category" => array(
				"className" => "Category",
				"conditions" => "",
				"foreignKey" => "category_id"
			)
		);

	/**
	 * キャッシュ
	 *
	 * @var bool
	 */
	public $cacheQueries = true;


// 要メソッド名の再考

	/**
	 * すべてのサイトを取得
	 *
	 * 未登録、削除済み、使用不可のサイトも含める
	 *
	 * @return array $sites サイトの配列
	 *						$sites[0] ('name' = > 'サイト名')
	 */
	public function getAllSites($categoryId = null) {
		$conditions = array();
		if ( !is_null($categoryId)) {
			$conditions['Site.category_id'] = $categoryId;
		}

		$options = array(
				'conditions' => $conditions,
				'order' => 'Site.id ASC'
		);

		$results = $this->find('all', $options);

		// 配列に移し替え
		$sites = array();
		foreach ($results as $data) {
			array_push($sites, $data['Site']);
		}

		return $sites;
	}

	/**
	 * 特定のカテゴリのサイトを取得
	 *
	 * 未登録、削除済み、使用不可のサイトは除く
	 *
	 * @return int   $categoryId カテゴリID nullの場合はすべてのカテゴリ
	 * @return array $sites サイトの配列
	 *						$sites[0] ('name' = > 'サイト名')
	 */
	public function getSites($categoryId = null) {
		$conditions = array(
				'Site.is_registered' => true,
				'Site.is_deleted'    => false
		);

		if ( !is_null($categoryId)) {
			$conditions['Site.category_id'] = $categoryId;
		}

		$options = array(
				'conditions' => $conditions,
				'order' => 'Site.category_id ASC, Site.id ASC'
		);

		$results = $this->find('all', $options);

		// 配列に移し替え
		$sites = array();
		foreach ($results as $data) {
			array_push($sites, $data['Site']);
		}

		return $sites;
	}

	/**
	 * 未登録サイトを取得
	 *
	 *
	 * @param  int   $categoryId
	 * @return array $sites サイトの配列
	 *						$sites[0] ('name' = > 'サイト名')
	 */
	public function getUnRegiSites() {
		$sites = array();

		$conditions = array(
				'Site.is_registered' => false,
				'Site.is_deleted'    => false
		);

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

	/**
	 * 同じURLのデータが存在していなければデータ挿入
	 *
	 * @param  array $site
	 * @return bool | int レコードのID | 挿入できなければfalse
	 */
	public function saveIfNotExists($site) {
		/*
		$result = $this->hasAny(array('url' => $site['url']));
		if ($result == true) {
			return false;
		}
		*/

		$conditions = array('Site.url' => $site['url']);
		$options = array(
				'conditions' => $conditions
		);
		$results = $this->find('all', $options);

		$count = $this->getNumRows();
		if (0 < $count) {
			return false;
		}

		// なければ保存
		$this->create();

		if($this->save($site)) {
			return $this->getInsertID();
		} else {
			return false;
		}
	}

	/**
	 * 未登録サイトをすべて登録
	 *
	 */
	public function registerAll() {
		// 未登録サイトをすべて取得
		$sites = $this->getUnRegiSites();

		foreach ($sites as $i => $site) {
			$site['is_registered'] = true;

			$this->save($site);
		}
	}

	/**
	 * 削除処理
	 * is_deleted を trueに編集
	 *
	 * @param  array $site
	 * @return bool
	 */
	public function checkDeleted($site) {

		// 同じidのデータがなければ終了
		if ( !$this->exists($site['id'])) {
			return false;
		}

		$site['is_deleted'] = true;

		if($this->save($site)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 複数のサイトを更新
	 *
	 * @param array $sites
	 */
	public function updateSites($sites) {

		foreach ($sites as $site) {
			$this->save($site);
		}
	}
}