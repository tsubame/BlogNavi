<?php
/**
 *
 *
 */
Class AllTests extends CakeTestSuite  {
//Class AllTests extends PHPUnit_Framework_TestSuite  {

	/**
	 * 全テスト
	 */
	public static function suite() {
		$suite = new CakeTestSuite('all test');

		//$suite->addTestDirectory(APP_TEST_CASES . DS . 'Controller');
		//$suite->addTestDirectory(APP_TEST_CASES . DS . 'Model');

		return $suite;
	}

}