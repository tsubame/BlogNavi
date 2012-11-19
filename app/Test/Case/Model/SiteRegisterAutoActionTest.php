<?php

App::uses('SiteRegisterAutoAction', 'Model');
App::uses('SiteRegisterAutoActionExtend', 'Model');

/**
 *
 *
 *
 */
class SiteRegisterAutoActionTest extends CakeTestCase  {

	private $SiteRegisterAutoAction;

	public function setUp() {
		parent::setUp();
		$this->SiteRegisterAutoAction = ClassRegistry::init('SiteRegisterAutoActionExtend');
	}

	/**
	 *
	 * @test
	 */
	public function execTest() {
		$this->SiteRegisterAutoAction->exec();
	}

	/**
	 *
	 * test
	 */
	public function autoCategorize() {
		$this->SiteRegisterAutoAction->autoCategorize();
	}

	/**
	 *
	 * test
	 */
	public function getLivedoorRankSites() {
		$sites = $this->SiteRegisterAutoAction->getLivedoorRankSites();

		debug($sites);
	}

	/**
	 *
	 * test
	 */
	public function getFc2RankSites() {
		$sites = $this->SiteRegisterAutoAction->getFc2RankSites();

		debug($sites);
	}

}

class SiteRegisterAutoActionExtend extends SiteRegisterAutoAction {

	public function getFc2RankSites() {
		return parent::getFc2RankSites();
	}

	public function getLivedoorRankSites() {
		return parent::getLivedoorRankSites();
	}

}