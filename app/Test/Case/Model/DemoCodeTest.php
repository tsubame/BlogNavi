<?php

App::uses('TwitterAPIAccessor', 'Model');
App::uses('ComponentCollection', 'Controller');
App::uses('HttpUtilComponent', 'Controller/Component');

/**
 *
 *
 * @author hid
 *
 */
class DemoCodeTest extends CakeTestCase  {

	private $TwitterAPIAccessor;

	private $httpUtil;

	public function setUp() {
		parent::setUp();
		$this->TwitterAPIAccessor = ClassRegistry::init('TwitterAPIAccessorExtend');

		$collection = new ComponentCollection();
		$this->httpUtil = new HttpUtilComponent($collection);
	}


	/**
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
	 * FC2 ブログ登録
	 *
	 * @test
	 */
	public function blogRegisterFc2() {

		$rankUrl = 'http://blog.fc2.com/subgenre/250/';
		$pattern = '/http:\/\/[\w]+\.blog[\d]*\.fc2\.com\//';

		$html = $this->httpUtil->getContents($rankUrl);

		$siteUrls = array();

		if (preg_match_all($pattern, $html, $matches)) {

			foreach ($matches[0] as $i => $url) {
				if (30 <= $i) {
					break;
				}

				// echo "$i => $url <br />";
				$siteUrls[] = $url;
 			}
		}

		$tw = new TwitterAPIAccessor();
		$counts = $tw->getTweetCountOfUrls($siteUrls);

		debug($counts);
	}

	/**
	 * ライブドア ブログ登録
	 *
	 * @test
	 */
	public function blogRegisteLivedoor() {

		$rankUrl = 'http://blog.livedoor.com/category/9/';
		$html = $this->httpUtil->getContents($rankUrl);

		$tagPattern = '/<h3[\s]+class\="ttl">.+?<\/h3>/is';
		$urlPattern = '/http:\/\/[\w\/\.\-_]+/is';

		$registerCount = 50;

		$siteUrls = array();
		if (preg_match_all($tagPattern, $html, $tags)) {

			foreach ($tags[0] as $i => $tag) {
				if ($registerCount < $i) {
					break;
				}

				if (preg_match($urlPattern, $tag, $urlMatchs)){
					$url = $urlMatchs[0];

					$siteUrls[] = $url;
				}
			}
		}

		$tw = new TwitterAPIAccessor();
		$counts = $tw->getTweetCountOfUrls($siteUrls);

		debug($counts);
	}


	/**
	 * 登録ロジック
	 */
	public function register() {

	// 定期的に？
		// ライブドアブログランキング

		// 人気ブログランキング

		// fc2

		// アメブロ

		//


	// 記事の取得





	// 要件
		// ツイート件数が一定以上のブログのみ登録

		// 定期的にブログ入れ替え

		// 選手のブログも追加（手動）


	}
}