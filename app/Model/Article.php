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





}