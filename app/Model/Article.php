<?php
/**
 * モデルクラス articlesテーブル
 *
 *
 * テーブル構成
 *
 * id		integer
 * name		varchar
 * url		varchar
 * rss_url	varchar
 * kind		varchar
 * enabled	boolean
 * created  datetime
 * modified datetime
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