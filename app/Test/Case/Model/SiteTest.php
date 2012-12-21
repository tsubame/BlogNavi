<?php

App::uses('Site', 'Model');

/**
 *
 *
 * @author hid
 *
 */
class SiteTest extends CakeTestCase  {

	private $Site;

	public $fixtures = array('article', 'site', 'category');

	public function setUp() {
		parent::setUp();
		$this->Site = ClassRegistry::init('Site');
	}

	public function tearDown() {
		unset($this->Site);
		parent::tearDown();
	}


	/**
	 *
	 * @test
	 */
	public function registerAll() {

		$this->Site->registerAll();
	}

	/**
	 *
	 * @test
	 */
	public function getIdFromUrl() {
		$url = 'http://ameblo.jp/okazaki-shinji/';

		$id = $this->Site->getIdFromUrl($url);
		$this->assertNotEqual($id, false);

		$url = 'abc';

		$id = $this->Site->getIdFromUrl($url);
		$this->assertEqual($id, false);
	}

	/**
	 *
	 * @test
	 */
	public function saveIfNotExists() {
		$rand = rand(0, 999999);

		$site = array(
				'name' => 'test',
				'url' => 'http://test/' . $rand);

		$result = $this->Site->saveIfNotExists($site);
		$this->assertNotEqual($result, false);

		$result = $this->Site->saveIfNotExists($site);
		$this->assertEqual($result, false);
	}

	/**
	 *
	 * @test
	 */
	public function getUnregiSites() {

		$sites = $this->Site->getUnregiSites();
		//debug(count($sites));
	}

	/**
	 *
	 * @test
	 */
	public function checkDeleted() {
		$site = array('id' => 41);
		$res = $this->Site->checkDeleted($site);

		debug($res);
		$this->assertEqual($res, true);
	}

	/**
	 * 更新処理
	 *
	 * @test
	 */
	public function update() {
		$site = array(
					'id' => 41,
					'url' => 'http://testUpdate'
				);

		if (isset($site['id'])) {
			$res = $this->Site->save($site);
		}

		//debug($res);

		$conditions = array('Site.id' => 41);
		$options = array(
				'conditions' => $conditions
		);
		$result = $this->Site->find('all', $options);
//debug($result);
		$this->assertEqual($result[0]['Site']['url'], 'http://testUpdate');
	}

	/**
	 * 複数件挿入のパフォーマンスチェック
	 *
	 * @test
	 */
	public function checkPerformance() {
		$ts = time();
		$dataCount = 500;
		$sites = array();

		// 挿入用データ
		for ($i = 0; $i <= $dataCount; $i++) {
			$site = array();
			$site['url'] = 'http://test.test.' . $i;
			$sites[] = $site;

			$this->Site->saveIfNotExists($site);
		}

		$saveTime = time() - $ts;
		debug("{$dataCount}件の挿入にかかった時間は{$saveTime}秒");
	}

}