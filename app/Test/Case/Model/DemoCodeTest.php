<?php

App::uses('TwitterAPIAccessor', 'Model');
App::uses('ComponentCollection', 'Controller');
App::uses('HttpUtilComponent', 'Controller/Component');
App::uses('RssFetcherComponent', 'Controller/Component');
/**
 *
 *
 * @author hid
 *
 */
class DemoCodeTest extends CakeTestCase  {

	private $TwitterAPIAccessor;

	private $httpUtil;
	private $rssFetcher;

	public function setUp() {
		parent::setUp();
		$this->TwitterAPIAccessor = ClassRegistry::init('TwitterAPIAccessorExtend');

		$collection = new ComponentCollection();
		$this->httpUtil = new HttpUtilComponent($collection);
		$this->rssFetcher = new RssFetcherComponent($collection);
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
	 * @test
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