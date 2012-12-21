<?php

App::uses('RssUtilComponent', 'Controller/Component');
App::uses('CurlComponent', 'Controller/Component');
App::uses('TwApiAccessorComponent', 'Controller/Component');

/**
 * SitesControllerのregisterFromSEngineアクション
 *

 *
 *
 * 依存クラス
 * ・Model/Site
 * ・Model/Article
 * ・Component/RssUtilComponent
 * ・Component/CurlComponent
 * ・Component/TwApiAccessorComponent
 *
 * エラー
 *
 */
class SiteRegisterFromSEngineAction extends AppModel {

	/**
	 * テーブルの使用
	 *
	 * @var bool
	 */
	public $useTable = false;

	/**
	 * キーワード抽出APIのURL
	 *
	 * @var string
	 */
	const YAHOO_KEYWORD_API = 'http://jlp.yahooapis.jp/KeyphraseService/V1/extract';

	/**
	 * APIから受け取ったデータの出力形式
	 *
	 */
	const API_OUT_PUT = 'xml';

	/**
	 * APIリクエスト用のURL
	 *
	 * @var string
	 */
	private $apiUrlWithKey;

	/**
	 * キーワードの最小の文字数
	 * これ未満のキーワードは取得しない
	 *
	 */
	const MIN_WORD_STR_COUNT = 3;


	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * 処理実行
	 *
	 */
	public function exec() {



	}

	/**
	 * ブログの記事タイトルからヤフーAPIで重複キーワード抽出
	 */
	protected function pickupMultiWordFromArticleTitle() {
		$catId = 0;
		// 24時間以内の記事取得
		$model   = ClassRegistry::init('Article');
		$results = $model->selectTodaysArticles($catId);

		// APIのURLにパラメータをくっつける
		$apiUrlWithParam = self::YAHOO_KEYWORD_API . "?appid=" .
				Configure::read('YAHOO_APP_ID') . "&output=" . self::API_OUT_PUT;

		// URLの配列を作成
		$urls = array();
		foreach ($results as $result) {
			$title = urlencode($result['Article']['title']);
			$url = $apiUrlWithParam . "&sentence={$title}";
			$urls[] = $url;
		}
		// 並列にAPIにリクエスト
		$collection = new ComponentCollection();
		$curl    = new CurlComponent($collection);
		$results = $curl->getContents($urls);

		// キーワード抽出
		$keywords   = array();
		$wordScores = array();
		foreach ($results as $result) {
			$xml = simplexml_load_string($result);

			foreach ($xml->Result as $value) {
				$keyword    = (string)$value->Keyphrase;
				$keywords[] = $keyword;
				$wordScores[$keyword] = (int)$value->Score;;
			}
		}
		arsort($wordScores, SORT_NUMERIC);
debug($wordScores);

		// 重複キーワード抽出
		$multiWords = $this->pickupMultiWords($keywords);

		return $multiWords;
	}

	/**
	 * キーワードから重複している物を取り出す
	 *
	 * @param  array $keywords
	 * @return array $multiWords
	 */
	protected function pickupMultiWords($keywords) {
		$multiWords = array();

		for($i = 0; $i < count($keywords); $i++) {

			$keyword = $this->getSimularShortWord($keywords, $keywords[$i]);
			$count = $this->getSimularWordCount($keywords, $keyword);

// 文字数が短すぎればスキップ
if (mb_strlen($keyword) < self::MIN_WORD_STR_COUNT) {
	continue;
}

			if ($count <= 1) {
				continue;
			}

			// 重複単語の配列に値が存在するか調べる
			if (isset($multiWords[$keyword])) {
				continue;
			} else {
				$multiWords[$keyword] = $count;
			}
		}
		arsort($multiWords, SORT_NUMERIC);

		return $multiWords;
	}

	/**
	 *
	 * @param unknown_type $haystack
	 * @param unknown_type $needle
	 */
	protected function compareSimularWords($haystack, $needle) {
		if (strpos($haystack, $needle) !== false) {
			return true;
		} elseif(strpos($needle, $haystack) !== false) {
			return true;
		}

		return false;
	}

	/**
	 *
	 * @param unknown_type $words
	 * @param unknown_type $word
	 */
	protected function getSimularWordCount($words, $word) {
		$count = 0;
		foreach ($words as $targetWord) {
			if (strpos($targetWord, $word) !== false) {
				$count ++;
			} elseif(strpos($word, $targetWord) !== false) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * 配列の中で似ている単語のうち、最も文字数の少ないものを取り出す
	 *
	 * @param unknown_type $words
	 * @param unknown_type $word
	 */
	protected function getSimularShortWord($words, $word) {
		$shortWord = $word;

		foreach ($words as $targetWord) {
			if(strpos($shortWord, $targetWord) !== false) {
				$shortWord = $targetWord;
			}
		}

		return $shortWord;
	}
}