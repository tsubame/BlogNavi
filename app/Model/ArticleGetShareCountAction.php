<?php

//App::uses('AppModel', 'Model');

/**
 * ArticlesControllerのgetShareCountアクション
 *
 * 書く記事のリツイート数を取得してarticlesテーブルを更新
 *
 *
 * 依存クラス
 * ・Article
 * ・TwitterAPIAccessor
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


// 要修正 コードに無駄がありそう
	/**
	 * 書く記事のツイート数を取得して設定
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

		// 記事のツイート数取得
		// 要 コンポーネントに書き換え
		$twAccessor  = ClassRegistry::init('TwitterAPIAccessor');
		$tweetCounts = $twAccessor->getTweetCountOfUrls($urls);

// ここから差し替えたい

		$savedArticles = array();
		foreach ($tweetCounts as $url => $count) {
			foreach ($articles as $article) {
				if ($url == $article['url']) {
					$article['tweeted_count'] = $count;
					$savedArticles[] = $article;
				}
			}
		}

		return $savedArticles;

// ここまで

// テストして大丈夫そうなら以下のコードに差し替え
/*
// ここ無駄なループがない？

		foreach ($tweetCounts as $url => $count) {
			foreach ($articles as $i => $article) {
				if ($url == $article['url']) {
					$articles[$i]['tweeted_count'] = $count;

					break;
				}
			}
		}

		return $articles;
//
 */
	}

}