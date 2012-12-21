<?php
App::uses('RssUtilComponent', 'Controller/Component');

/**
 * 記事登録処理
 *
 * ArticleコントローラのRegisterアクションのロジック
 *
 * 依存クラス
 * ・Component/RssUtilComponent
 *
 * エラー
 * ・？？
 *
 */
class ArticleRegisterAction extends AppModel {

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
		// sitesテーブルからサイトを取得
		$model = ClassRegistry::init('Site');
		$sites = $model->getSites();
		// フィードURLの配列作成
		$feedUrls = $this->createFeedUrlsArray($sites);
		// 各URLのRSSフィードを取得
		$collection  = new ComponentCollection();
		$fetcher     = new RssUtilComponent($collection);

		// 削除予定
		$beforeTs = time();
//debug(time());

		$feedOfSites = $fetcher->getFeedAndParse($feedUrls);

		$articles = array();

		foreach ($feedOfSites as $i => $feedOfSite) {
			foreach ($feedOfSite as $article) {
				$article['site_id'] = $sites[$i]['id'];
				// URLをデコード
				$article['url'] = urldecode($article['url']);
				$articles[] = $article;
			}
		}
		$model = ClassRegistry::init('Article');
		$saveArticles = $this->pickupSaveArticle($articles);
		try {
			$model->saveAll($saveArticles);
		} catch(Exception $e) {
			debug($e->getMessage());
		}

		$saveCount = count($saveArticles);

		// 削除予定
		$execTime = time() - $beforeTs;
		debug("最終処理時間：{$execTime}秒");
		debug("{$saveCount}件の記事を登録しました。");
		// ロギング
		CakeLog::info("{$saveCount}件の記事を登録しました。");
	}


	/**
	 * 処理実行
	 * 旧コード
	 *
	 */
	public function execPreveious() {
		// sitesテーブルからサイトを取得
		$model = ClassRegistry::init('Site');
		$sites = $model->getSites();
		// フィードURLの配列作成
		$feedUrls = $this->createFeedUrlsArray($sites);
		// 各URLのRSSフィードを取得
		$collection  = new ComponentCollection();
		$fetcher     = new RssUtilComponent($collection);
		// 削除予定
		$beforeTs = time();
		debug(time());

		$feedOfSites = $fetcher->getFeedAndParse($feedUrls);
		debug(time());

		$saveCount = 0;

		// 記事を登録
		foreach ($feedOfSites as $i => $feedOfSite) {
			foreach ($feedOfSite as $article) {
				$article['site_id'] = $sites[$i]['id'];
				// URLをデコード
				$article['url'] = urldecode($article['url']);

				$result = $this->saveArticle($article);

				if ($result !== false) {
					$saveCount++;
				}
			}
		}

		// 削除予定
		$execTime = time() - $beforeTs;
		debug("最終処理時間：{$execTime}秒");
		// ロギング
		CakeLog::info("{$saveCount}件の記事を登録しました。");
	}
	/**
	 * サイトの配列を受け取ってフィードURLの配列を返す
	 * フィードURLがないサイトはURLを入れる
	 *
	 * @param  array $sites
	 * @return array $feedUrls
	 */
	protected function createFeedUrlsArray($sites) {
		$feedUrls = array();
		// フィードURLの配列を作成 なければサイトのURL
		foreach ($sites as $i => $site) {
			if (isset($site['feed_url'])) {
				array_push($feedUrls, $site['feed_url']);
			} else {
				array_push($feedUrls, $site['url']);
			}
		}

		return $feedUrls;
	}


	/**
	 * 登録処理
	 * 記事の発行日時が○時間以上前の記事は登録しない
	 *
	 * 何時間前の記事から登録するかは定数にて決める
	 *
	 * @see    const->Article.registerPastHourFrom
	 * @param  array $article
	 * @return bool
	 */
	protected function saveArticle($article) {
		$pubTs = strtotime($article['published']);
		$intervalSec = Configure::read('Article.registerPastHourFrom') * 3600;
		$nowTs = time();

		$model = ClassRegistry::init('Article');

		if (($nowTs - $pubTs) < $intervalSec) {
			$result = $model->saveIfNotExists($article);
			// 登録できればtrueを返す
			if ($result !== false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * 登録する記事を選別する
	 *
	 * 登録済みの記事はスルー
	 * 記事の発行日時が○時間以上前の記事は登録しない
	 *
	 * 何時間前の記事から登録するかは定数にて決める
	 *
	 * @see    const->Article.registerPastHourFrom
	 * @param  array $article
	 * @return bool
	 */
	protected function pickupSaveArticle($articles) {
		$saveArticles = array();
		$model = ClassRegistry::init('Article');

		foreach ($articles as $article) {
			// 日付
			$pubTs = strtotime($article['published']);
			$intervalSec = Configure::read('Article.registerPastHourFrom') * 3600;
			$nowTs = time();

			if (($nowTs - $pubTs) < $intervalSec) {
				// ユニークかチェック
				$model->create();
				$model->set($article);
				$res = $model->isUnique(array('url'));
				if ($res == true) {
					$saveArticles[] = $article;
				}
			}
		}

		return $saveArticles;
	}

}