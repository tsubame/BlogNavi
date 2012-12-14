<?php
App::uses('TwApiAccessorComponent', 'Controller/Component');

/**
 * ArticlesControllerのgetShareCountアクション
 *
 * 24時間以内の記事のRT数を取得してarticlesテーブルを更新
 *
 *
 * 依存クラス
 * ・Model/Article
 * ・Component/TwApiAccessorComponent
 *
 * エラー
 *
 * ・？
 *
 */
class ArticleGetShareCountAction extends AppModel {

	/**
	 * 同名のテーブルの使用
	 *
	 * @var bool
	 */
	public $useTable = false;


	/**
	 * 処理実行
	 *
	 */
	public function exec() {
		// 記事取得
		$articleModel = ClassRegistry::init('Article');
		$articles = $articleModel->selectTodaysAllArticles();
		// 記事のツイート数取得
		$savedArticles = $this->getTweetCounts($articles);

		// 更新
		$articleModel->saveAll($savedArticles);
	}

	/**
	 * 各記事のツイート数を取得して設定
	 *
	 * @param  array $articles
	 * @return array $savedArticles
	 */
	protected function getTweetCounts($articles) {
		// 記事のURLの配列作成
		$urls = array();
		foreach ($articles as $article) {
			$urls[] = $article['url'];
		}
		// 記事のRT数取得
		$collection = new ComponentCollection();
		$twAccessor = new TwApiAccessorComponent($collection);
		$tweetCounts = $twAccessor->getTweetCountOfUrls($urls);

		$i = 0;
		foreach ($tweetCounts as $url => $count) {

			$result = $this->checkUrlMatch($url, $articles[$i]['url']);
			//if ($url == $articles[$i]['url']) {
			if ($result == true) {
				$articles[$i]['tweeted_count'] = $count;
			} else {
				$articles[$i]['tweeted_count'] = 0;
			}

			$i++;
		}

		return $articles;
	}

	/**
	 * ２つのURLが同じページかどうかを調べる
	 *
	 * @param string $url1
	 * @param string $url2
	 * @return bool
	 */
	protected function checkUrlMatch($url1, $url2) {
		//http://と最後の/を除く
		$url1 = str_replace('http://', '', $url1);
		$url2 = str_replace('http://', '', $url2);

		if ($url1 == $url2) {
			return true;
		} else if ($url1 == $url2 . '/') {
			return true;
		} else if ($url1 . '/' == $url2) {
			return true;
		}

		return false;
	}

}