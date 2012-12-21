<?php

App::uses('SiteExportAction', 'Model');

/**
 *
 *
 *
 */
class SiteExportActionTest extends CakeTestCase  {

	private $action;

	public $fixtures = array('article', 'site', 'category');

	public function setUp() {
		parent::setUp();
		$this->action = ClassRegistry::init('SiteExportAction');
	}

	public function tearDown() {
		unset($this->action);
		parent::tearDown();
	}


	/**
	 *
	 * @test
	 */
	public function execTest() {
		$this->action->exec();
	}

	/**
	 * export() 正常系
	 *
	 * test
	 */
	public function export() {
		// ファイル名を定数から取得
		$fileNames = Configure::read('Site.fileNames');

		$ref = new ReflectionMethod('SiteExportAction', 'export');
		$ref->setAccessible(true);

		// 各ファイルから読み込んで登録
		foreach ($fileNames as $i => $fileName) {
			$this->action->export($i, $fileName);

			$result = $ref->invoke(new SiteExportAction(), $i, $fileName);
		}
	}

}

