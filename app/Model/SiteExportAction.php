<?php

/**
 * SitesControllerのexportアクション
 *
 * sitesテーブル内のサイトをファイルに書き出す
 *
 * ファイルの書き方の例 （URLと名前を空白で区切って1行ずつ書く）
 *
 *   http://yahoo.co.jp/ ヤフー
 *   http://google.co.jp/ google
 *
 */
class SiteExportAction extends AppModel {

	/**
	 * テーブルの使用
	 *
	 * @var bool
	 */
	public $useTable = false;

	/**
	 * エラーメッセージ
	 *
	 * @var string
	 */
	private $errorMessage;


	/**
	 * 処理実行
	 */
	public function exec() {
		// ファイル名を定数から取得
		$fileNames = Configure::read('Site.fileNames');

		// 各ファイルから読み込んで登録
		foreach ($fileNames as $i => $fileName) {
			$this->export($i, $fileName);
		}
	}

	/**
	 * ファイルへの書き出し
	 *
	 * ファイルの書き方の例
	 *
	 *   http://yahoo.co.jp/ ヤフー
	 *   http://google.co.jp/ google
	 *
	 * @param  int    $catId    カテゴリ番号
	 * @param  string $fileName ファイル名
	 * @return bool エラー時はfalse
	 */
	protected function export($catId = 1, $fileName) {
		$siteModel = ClassRegistry::init('Site');
		$sites = $siteModel->getSites($catId);

		$text = '';
		// 文字列作成
		foreach ($sites as $site) {
			$text .= "{$site['url']} {$site['name']}\n";
		}

		try {
			$filePath = Configure::read('Site.fileDirPath') . $fileName;
			file_put_contents($filePath, $text);
		// 例外時は警告をロギング
		} catch(Exception $e) {
			CakeLog::warning("ファイルに書き込めません " . $e->getMessage());
			$this->errorMessage =  $e->getMessage();

			return false;
		}

		return true;
	}

	/**
	 *
	 * @return string
	 */
	public function getErrorMessage() {
		return $this->errorMessage;
	}
}