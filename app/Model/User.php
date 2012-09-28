<?php

/**
 * モデルクラス
 *
 *
 *
 */
App::uses('AppModel', 'Model');

class User extends AppModel{

	public $useTable = 'false';

	// モデルに必要なプロパティ、メソッドは？



	/**
	 * すべてのサイトを取得
	 *
	 * @return array $sites サイトの配列
	 *						$sites[0] ('name' = > 'サイト名')
	 */
	public function test() {
		debug('test');
		return null;
	}


}