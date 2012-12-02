<?php
App::uses('ComponentCollection', 'Controller');
App::uses('RssUtilComponent',    'Controller/Component');

/**
 * SitesControllerのregisterFromFileアクション
 *
 * ファイルからサイトを登録する
 *
 *
 * 仕様
 *
 * ・webroot/file/以下のファイルを使用
 * ・1行に1つサイトのURLを書く
 * ・URLの後にスペースでサイト名を書くことができる。ない場合はRSSからサイト名を自動取得
 *
 * ファイルの書き方の例
 *
 *   http://yahoo.co.jp/ ヤフー
 *   http://google.co.jp/ google
 *
 * 依存クラス
 *
 * ・Component/RssUtilComponent
 * ・ComponentCollection
 * ・Model/Sites
 *
 * エラー処理
 *
 * ・ファイルが開けない        → ログにエラーメッセージを記録
 * ・書式がおかしい行がある    → ログに警告メッセージを記録
 * ・フィードURLが取得できない → ログに警告メッセージを記録してスキップ
 *
 */
class SiteRegisterFromFileAction extends AppModel {

	/**
	 * テーブルの使用
	 *
	 * @var bool
	 */
	public $useTable = false;


	/**
	 * 処理実行
	 *
	 */
	public function exec() {
		// ファイル名を定数から取得
		$fileNames = Configure::read('Site.fileNames');

		// 各ファイルから読み込んで登録
		foreach ($fileNames as $i => $fileName) {
			$this->register($i, $fileName);
		}
	}

	/**
	 * 登録処理
	 *
	 * ファイルからサイトを読み込んでDBに保存する
	 *
	 * @param  int    $catId    カテゴリ番号
	 * @param  string $fileName ファイル名
	 */
	protected function register($catId = 1, $fileName) {
		// ファイル内のテキストを取得
		try {
			//$filePath = WWW_ROOT . 'files' . DS . $fileName;
			$filePath = Configure::read('Site.fileDirPath') . $fileName;
			$text = file_get_contents($filePath);
		// 例外時は警告をロギング
		} catch(Exception $e) {
			CakeLog::warning("ファイルが開けません " . $e->getMessage());

			return;
		}

		$siteModel = ClassRegistry::init('Site');
		// テキストから配列にURL、サイト名を取得
		$sites = $this->splitUrlAndSiteName($text);

		// サイトのフィードURL、サイト名をRSSで取得する
		foreach ($sites as $site) {
			$site = $this->getFeedUrlAndSiteName($site);

			if ($site == false) {
				continue;
			}

			$site['category_id']     = $catId;
			$site['registered_from'] = 'file';

			// DBに保存
			$siteModel->saveIfNotExists($site);
		}
	}

	/**
	 * サイトのフィードURL、サイト名をRSS経由で取得する
	 *
	 * サイト名はnullでない時のみ（ファイルに書いていないとき）取得する
	 *
	 * @param array $site フィードURL取得不可の時はfalse
	 */
	protected function getFeedUrlAndSiteName($site) {
		// フィードURLを取得
		$collection = new ComponentCollection();
		$rssUtil    = new RssUtilComponent($collection);

		$feedUrl = $rssUtil->getFeedUrlFromSiteUrl($site['url']);

		if ($feedUrl != false) {
			$site['feed_url'] = $feedUrl;
		} else {
			// 警告をロギング
			CakeLog::warning("フィードURLを取得できませんでした {$url}");
			return false;
		}

		// サイト名を取得
		if (is_null($site['name'])) {
			$siteName = $rssUtil->getSiteName($feedUrl);

			if ($siteName != false) {
				$site['name'] = $siteName;
			} else {
				CakeLog::warning("サイト名を取得できませんでした {$url}");
			}
		}

		return $site;
	}

	/**
	 * 複数行のテキストからURLとサイト名を取り出す
	 * サイト名がないときはnullをセット
	 *
	 * @param  string $text
	 * @return array  $sites
	 * 					$sites => array(
	 * 						[0] => array(
	 * 							['url']  => URL,
	 * 							['name'] => サイト名
	 * 						)
	 * 					)
	 */
	protected function splitUrlAndSiteName($text) {
		$urlPattern = Configure::read('urlPattern');
		$sites = array();

		// 配列にURL、名前を取得
		$lines = explode("\n", $text);
		// 行数分ループ
		foreach ($lines as $i => $line) {
			$line = trim($line);

			$site = array();
			// URLを抽出
			if ( !preg_match($urlPattern, $line, $matches)) {
				// ロギング 書式がおかしいです URLがありません
				CakeLog::warning("ファイルの行にURLがありません " . $line);
				continue;
			}
			$site['url'] = $matches[0];

			// サイト名を抽出
			$siteName = str_replace($site['url'], '', $line);
			if ($siteName != '') {
				$site['name'] = trim($siteName);
			} else {
				$site['name'] = null;
			}

			$sites[] = $site;
		}

		return $sites;
	}


}