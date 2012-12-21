<?php
App::uses('SiteRegisterFromRankAction', 'Model');

/**
 *
 *
 *
 */
class SiteRegisterFromRankActionTest extends CakeTestCase  {

	private $action;

	public function setUp() {
		parent::setUp();
		$this->action = ClassRegistry::init('SiteRegisterFromRankAction');
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function exec() {
		$this->action->exec();
	}

	/**
	 * 正常系
	 *
	 * test
	 */
	public function autoCategorize() {
		$ref = new ReflectionMethod('SiteRegisterFromRankAction', 'autoCategorize');
		$ref->setAccessible(true);
		$ref->invoke(new SiteRegisterFromRankAction());
	}

	/**
	 * 正常系
	 *
	 * test
	 */
	public function getLivedoorRankSites() {
		$ref = new ReflectionMethod('SiteRegisterFromRankAction', 'getLivedoorRankSites');
		$ref->setAccessible(true);
		$sites = $ref->invoke(new SiteRegisterFromRankAction());

		debug($sites);
	}

	/**
	 * 正常系
	 *
	 * test
	 */
	public function getFc2RankSites() {
		$ref = new ReflectionMethod('SiteRegisterFromRankAction', 'getFc2RankSites');
		$ref->setAccessible(true);
		$sites = $ref->invoke(new SiteRegisterFromRankAction());

		debug($sites);
	}

	/**
	 * 正常系
	 *
	 * @test
	 */
	public function getAmebaRankSites() {
		$ref = new ReflectionMethod('SiteRegisterFromRankAction', 'getAmebaRankSites');
		$ref->setAccessible(true);
		$sites = $ref->invoke(new SiteRegisterFromRankAction());

		debug($sites);

// サイトの件数ループ

// URLの配列作成

// RSSを取得



	}

}