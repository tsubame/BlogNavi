<?php
/**
 * コントローラ
 *
 *
 */
class SitesController extends Controller {

	//public $name = "Sites";

	public $helpers = array('Form');

	/**
	 *
	 */
	public function index() {

		$this->render("index");

	}

	/**
	 * 登録フォームから手動でサイトを登録
	 *
	 */
	public function registerFromForm() {

	}

	/**
	 * 登録フォーム
	 *
	 */
	public function registerForm() {

	}

	/**
	 * googleから登録
	 */
	public function registerFromGoogle() {


	}

}