<?php
/**
 *
 *
 */
Class AllTests extends PHPUnit_Framework_TestSuite {

	/**
	 * 全テスト
	 */
	public static function suite() {
		$suite = new CakeTestSuite('アプリケーション全テスト');
		
		$suite->addTestDirectory(APP_TEST_CASES . DS . 'Controller');
		//$suite->addTestDirectory(APP_TEST_CASES . DS . 'Model');

		return $suite;
	}

}