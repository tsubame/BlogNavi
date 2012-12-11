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
		$this->action = ClassRegistry::init('SiteRegisterFromRankActionExtend');
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
		$this->action->autoCategorize();
	}

	/**
	 * 正常系
	 *
	 * test
	 */
	public function getLivedoorRankSites() {
		$sites = $this->action->getLivedoorRankSites();

		debug($sites);
	}

	/**
	 * 正常系
	 *
	 * test
	 */
	public function getFc2RankSites() {
		$sites = $this->action->getFc2RankSites();

		debug($sites);
	}

}

/**
 * protectedメソッドテスト用
 *
 *
 */
class SiteRegisterFromRankActionExtend extends SiteRegisterFromRankAction {

	public function getFc2RankSites() {
		return parent::getFc2RankSites();
	}

	public function getLivedoorRankSites() {
		return parent::getLivedoorRankSites();
	}

}