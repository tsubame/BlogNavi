<?php
App::import('Vendor', 'simplepie/autoloader');
App::uses('ComponentCollection', 'Controller');
App::uses('CurlComponent', 'Controller/Component');


/**
 * RSSを取得するコンポーネント
 *
 * 要：SimplePieライブラリ
 *     Curlコンポーネント
 *
 *
 * エラー時の処理
 *
 *
 *
 */
class RssUtilComponent extends Component {

	public $components = array("Curl");

	/**
	 * @var object Instance of CurlComponent
	 */
	private $Curl;


	/**
	 * コンストラクタ
	 *
	 */
	public function __construct($collection) {
		parent::__construct($collection);

		$this->Curl = new CurlComponent($collection);
	}

	/**
	 * RSSフィードを取得して配列で返す
	 *
	 *
	 * 戻り値の配列の形式
	 *
	 * 	$entries = array(
	 * 		0 => array(
	 * 			'url' => URL,
	 * 			'title' => タイトル,
	 * 			'description' => サマリー,
	 * 			'published' => 作成日時
	 * 		)
	 * 	)
	 *
	 * @param  string $url 	   フィードURL or 記事のURL
	 * @return array  $entries フィード内のエントリの配列
	 */
	public function getFeed($url) {
		$simplePie = new SimplePie();
		$simplePie->set_feed_url($url);
		$simplePie->init();

		$entries = array();
//debug($simplePie->get_title());
//debug($simplePie->get_encoding());

		foreach ($simplePie->get_items() as $i => $item) {
			$entries[$i]['url']         = $item->get_permalink();
			$entries[$i]['title']       = $item->get_title();
			$entries[$i]['description'] = $item->get_description();
			$entries[$i]['published']   = $item->get_date('Y-m-d H:i:s');

			// &amp;を&に置き換え
			$entries[$i]['title']       = str_replace('&amp;', '&', $entries[$i]['title']);
			$entries[$i]['description'] = str_replace('&amp;', '&', $entries[$i]['description']);
		}

		return $entries;
	}

	/**
	 * 複数のURLのRSSフィードを並列に取得
	 *
	 * 戻り値の配列の形式
	 *
	 * 	$parsedFeeds = array(
	 * 		0 => array(
	 * 			0 => array(
	 * 				'url' => URL,
	 * 				'title' => タイトル,
	 * 				'description' => サマリー,
	 * 				'published' => 作成日時
	 * 			)
	 * 		)
	 * 	)
	 *
	 * @param  array $feedUrls フィードURLの配列
	 * @return array $parsedFeeds
	 */
	public function getFeedParallel($feedUrls) {
		// HTTPで並列にRSSフィードを取得
		$xmlRow = $this->Curl->getContents($feedUrls);

		$parsedFeeds = array();
		foreach ($xmlRow as $j => $xml) {
			$simplePie = new SimplePie();
			$simplePie->set_raw_data($xml);
			$simplePie->init();
			$simplePie->handle_content_type();

			//echo '<a href = "' . $feedUrls[$j] . '" target = "_blank">' . $simplePie->get_title() . '</a>';
			$entries = array();

			foreach ($simplePie->get_items() as $i => $item) {
				$entries[$i]['url']         = $item->get_permalink();
				$entries[$i]['title']       = $item->get_title();
				$entries[$i]['published']   = $item->get_date('Y-m-d H:i:s');
				$entries[$i]['description'] = $item->get_description();

// 文字化け対策
if (strpos($entries[$i]['title'], '&#') !== false) {
	debug('&#あり');
	echo $entries[$i]['title'] . '<br />';

	continue;
}

// &amp;を&に置き換え
//$entries[$i]['title'] = str_replace('&amp;', '&', $entries[$i]['title']);
//$entries[$i]['title'] = str_replace('&quot;', '"', $entries[$i]['title']);
$entries[$i]['title'] = htmlspecialchars_decode($entries[$i]['title'], ENT_QUOTES);
$entries[$i]['title'] = htmlspecialchars_decode($entries[$i]['title'], ENT_QUOTES);

$entries[$i]['description'] = str_replace('&amp;', '&', $entries[$i]['description']);

			}

			array_push($parsedFeeds, $entries);
		}

		return $parsedFeeds;
	}

	/**
	 * URLからフィードURLを取得
	 *
	 * 取得できないときはfalseを返す
	 *
	 * @param  string $url
	 * @return bool | string
	 */
	public function getFeedUrlFromSiteUrl($url) {

		$fc2RssSuffix = '?xml';
		$livedoorRssSuffix = 'index.rdf';
		$fc2Pattern = '/blog\.fc2\.com/';
		$livedoorPattern = '/livedoor/';

		if (preg_match($fc2Pattern, $url)) {
			return $url . $fc2RssSuffix;
		}
		if (preg_match($livedoorPattern, $url)) {
			return $url . $livedoorRssSuffix;
		}

		$simplePie = new SimplePie();
		$simplePie->set_feed_url($url);
		$simplePie->init();

		$type = $simplePie->get_type();
		if ($type == 0) {
			return false;
		}

		return $simplePie->feed_url;
	}

	/**
	 * URLからサイトの名前を取得
	 *
	 * 取得できないときはfalseを返す
	 *
	 * @param  string $url
	 * @return bool | string
	 */
	public function getSiteName($url) {
		$simplePie = new SimplePie();
		$simplePie->set_feed_url($url);
		$simplePie->init();

		$type = $simplePie->get_type();
		if ($type == 0) {
			return false;
		}

		// &amp;を&に置き換え
		$siteName  = str_replace('&amp;', '&', $simplePie->get_title());

		return $siteName;
	}

	/**
	 * サイトのURLを取得
	 *
	 * @param  string $url
	 * @return bool | string
	 */
	public function getSiteUrl($url) {
		$simplePie = new SimplePie();
		$simplePie->set_feed_url($url);
		$simplePie->init();

		$type = $simplePie->get_type();
		if ($type == 0) {
			return false;
		}

		return $simplePie->get_link();
	}

	/**
	 * URLからフィードURL、サイト名、トップページURLを取得
	 *
	 * @param  string $url
	 * @return aray $site
	 */
	public function getSiteInfo($url) {
		$site = array();

		$simplePie = new SimplePie();
		$simplePie->set_feed_url($url);
		$simplePie->init();

		$type = $simplePie->get_type();
		if ($type == 0) {
			return false;
		}

		$site['url']      = $simplePie->get_link();
		$site['name']     = $simplePie->get_title();
		$site['feed_url'] = $simplePie->feed_url;

		// &amp;を&に置き換え
		$site['name']   = str_replace('&amp;', '&', $site['name']);

		return $site;
	}

}
