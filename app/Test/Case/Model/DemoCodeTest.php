<?php

App::uses('TwitterAPIAccessor', 'Model');
App::uses('ComponentCollection', 'Controller');
App::uses('HttpUtilComponent', 'Controller/Component');
App::uses('CurlComponent', 'Controller/Component');
App::uses('RssUtilComponent', 'Controller/Component');
/**
 *
 *
 * @author hid
 *
 */
class DemoCodeTest extends CakeTestCase  {

	private $httpUtil;
	private $rssUtil;
	private $curl;

	/**
	 * fixture
	 *
	 * @var array
	 */
	public $fixtures = array('article', 'site');

	/**
	 * (non-PHPdoc)
	 * @see CakeTestCase::setUp()
	 */
	public function setUp() {
		parent::setUp();

		$collection = new ComponentCollection();
		//$this->httpUtil = new HttpUtilComponent($collection);
		$this->curl = new CurlComponent($collection);
		$this->rssUtil = new RssUtilComponent($collection);
	}

	/**
	 * (non-PHPdoc)
	 * @see CakeTestCase::tearDown()
	 */
	public function tearDown() {
		unset($this->curl);

		parent::tearDown();
	}


	/**
	 *
	 * test
	 */
	public function constTest() {
		debug(Configure::read('test2.test'));
		debug(Configure::read('test'));

		debug(Configure::read('Category.names'));
	}

	/**
	 * 日本語URLをそのまま格納
	 *
	 * @test
	 */
	public function useJapaneseUrl() {

		// 日本語URLをarticlesテーブルに保存
		$model = ClassRegistry::init('Article');

		$article = array();
		$article['id'] = 9999;
		//$article['url']  = 'http://www.goal.com/jp/news/74/イングランド/2012/12/22/3619777/香川が２４日からチーム練習合流';
		$article['url']  = 'http://www.goal.com/jp/news/74/%E3%82%A4%E3%83%B3%E3%82%B0%E3%83%A9%E3%83%B3%E3%83%89/2012/12/22/3619777/%E9%A6%99%E5%B7%9D%E3%81%8C%EF%BC%92%EF%BC%94%E6%97%A5%E3%81%8B%E3%82%89%E3%83%81%E3%83%BC%E3%83%A0%E7%B7%B4%E7%BF%92%E5%90%88%E6%B5%81';

		$article['url'] = urldecode($article['url']);
		$article['title'] = 'Goal.com テスト用';

		$model->save($article);
		// 取り出す
		$result = $model->findById(9999);

		debug($result);
		$url = $result['Article']['url'];
		$html = $this->curl->getContent($url);

		debug($html);
	}

	/**
	 * キーフレーズの抽出
	 *
	 * test
	 */
	public function pickupKeyPhrase() {
		$appId = 'J8nvyLixg676zBufLdmjXZ_rAEq3XeFgY5EG50w2P116X4QlCPVDTVa2bn0feuG7FTc-';
		$apiUrl = 'http://jlp.yahooapis.jp/KeyphraseService/V1/extract?';
		$output = 'xml';
		//$text = '本田圭佑本日の移籍報道まとめ ラツィオかマンUか？:本田△';

		$model = ClassRegistry::init('Article');
		$results = $model->selectTodaysArticles(2);

		$urls = array();

		foreach ($results as $result) {
			$title = $result['Article']['title'];
			$title = urlencode($title);
			$url = "{$apiUrl}appid={$appId}&output={$output}&sentence={$title}";
			$urls[] = $url;
		}
		$results = $this->curl->getContents($urls);
		$keywords = array();
		$multiWords = array();

		$wordScores = array();

		foreach ($results as $result) {
			$xml = simplexml_load_string($result);

			foreach ($xml->Result as $value) {
				$keyword = (string)$value->Keyphrase;
				$score   = (int)$value->Score;

				$keywords[] = $keyword;
				$wordScores[$keyword] = $score;
			}
		}

		while (0 < count($keywords)) {
			$word = array_pop($keywords);
			// 重複単語の配列に値が存在するか調べる

			if (isset($multiWords[$word])) {
				$multiWords[$word]++ ;
				continue;
			}

			$res = array_search($word, $keywords);

			if ($res !== false) {
				$multiWords[$word] = 1;
			}
		}

		arsort($wordScores, SORT_NUMERIC);
		arsort($multiWords, SORT_NUMERIC);
		debug($wordScores);
		debug($multiWords);
	}

	/**
	 *
	 * test
	 */
	public function pickupWordTest() {
		$model = ClassRegistry::init('Article');

		$results = $model->selectTodaysArticles(1, 30);

		$articleWords = array();

		$words = array();
		// 記事の件数ループ
		foreach ($results as $result) {
			$title = $result['Article']['title'];
			$pattern = '/[一-龠]+|[ァ-ヴー]+|[a-zA-Z0-9]+|[ａ-ｚＡ-Ｚ０-９]+/ius';

			if (preg_match_all($pattern, $title, $matches)) {

				$articleWords = array();

				foreach ($matches[0] as $word) {
					if (mb_strlen($word) < 2) {
						continue;
					} else {
						array_push($words, $word);
						array_push($articleWords, $word);
					}
				}

				debug($articleWords);
			}
		}

		debug($words);

		$hotWords = array();
		while(0 < count($words)) {
			$word = array_pop($words);

			foreach ($words as $tWord) {
				if ($word == $tWord || strpos($word, $tWord) !== false || strpos($tWord, $word) !== false) {
					if (array_search($word, $hotWords) !== false) {
						debug('値あり' . $word);
						break;
					}
					$hotWords[] = $word;

					break;
				}
			}
		}

		debug($hotWords);
	}

	/**
	 * Facebook
	 *
	 * test
	 */
	public function fbTest() {
		$url = 'http://number.bunshun.jp/';

		$fql = urlencode('SELECT total_count FROM link_stat WHERE url="' . $url . '"');

		$fql = urlencode('SELECT total_count FROM link_stat WHERE url="http://number.bunshun.jp/"');

		$url = 'https://api.facebook.com/method/fql.query?query=' . $fql;

		$url = 'https://api.facebook.com/method/fql.query?query=SELECT+total_count+FROM+link_stat+WHERE+url%3D%22http%3A%2F%2Fnumber.bunshun.jp%2F%22';

		$res = $this->httpUtil->getContents($url);

		echo $fql;

		//$response = file_get_contents('https://api.facebook.com/method/fql.query?query=SELECT+total_count+FROM+link_stat+WHERE+url%3D%22http%3A%2F%2Fnumber.bunshun.jp%2F%22');
		$xml = simplexml_load_string($res);

		$count =  $xml->link_stat->total_count;

		debug($count);
	}

	/**
	 * ヤフーブログ登録
	 *
	 * test
	 */
	public function yahooBlogSearch() {
		$appId = 'J8nvyLixg676zBufLdmjXZ_rAEq3XeFgY5EG50w2P116X4QlCPVDTVa2bn0feuG7FTc-';

		$apiUrl = 'http://search.yahooapis.jp/BlogSearchService/V1/blogSearch?';

		$q = '柿谷';

		$count = 50;

		$type = 'article'; //'article';

		$output = 'xml';

		$url = "{$apiUrl}query={$q}&results={$count}&appid={$appId}&output={$output}&type={$type}";

		echo $url;

		$res = file_get_contents($url);

		debug($res);
	}

	/**
	 *
	 * test
	 */
	public function demo2() {
		$dayAgo = -1;
		$dayAgoMinus = (string)($dayAgo * -1);

		$dateFrom = date('Y-m-d 0:00:00', strtotime($dayAgoMinus . ' day'));
		$dateTo = date('Y-m-d 23:59:59', strtotime($dayAgoMinus . ' day'));

		debug($dateFrom);
		debug($dateTo);
	}

	/**
	 *
	 * test
	 */
	public function preg2ch() {

		$text = '【サッカー/日本代表】2015南米選手権(コパ･アメリカ)に日本を招待へhttp://awabi.2ch.net/test/read.cgi/mnewsplus/13511289561れいおφ ★：2012/10/25(木) 10:35:56.77 ID:???0南米サッカー連盟は２４日、アルゼンチンで理事会を開き、２０１５年にチリで開く南米選手権...';

		$pattern = '/201[\d]\/[\d\/]+.{3}[\s][\d\.:]+[\s]*ID:/s';

		$pattern = '/201[\d]\/[\d\/]+[^\s]+[\d\s\.:]+ID:/s';

		if (preg_match($pattern, $text, $matches)) {
			debug($matches[0]);
		}
	}

	/**
	 *
	 *
	 */
	public function demo() {

		// ロギング
		$obj = ClassRegistry::init('Site');
		//$obj->log("Something didn't work!", LOG_DEBUG);
		CakeLog::debug("デバッグテストなう");
		CakeLog::info('info');
	}

}