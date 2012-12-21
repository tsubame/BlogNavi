<?php
App::import('Vendor', 'simplepie/autoloader');
App::import('Vendor', 'magpierss/rss_fetch.inc');
App::import('Vendor', 'lastRSS/lastRSS');
App::import('Vendor', 'lastRSS/lastRSSExtend');
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

	private $urls = array();

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
	 * フィードを取得して配列形式で返す
	 *
	 * 配列のデータ形式も書く
	 *
	 * @param  array $urls
	 * @return array
	 */
	public function getFeedAndParse($urls) {
		if (!is_array($urls)) {
			$url = $urls;
			$urls = array(0 => $url);
		}

		// Curlでデータ取得
		$xmlStrings = $this->Curl->getContents($urls);
		$entriesOfSites = array();
		// サイトの件数ループ
		foreach ($xmlStrings as $i => $xmlStr) {
			$xml = Xml::build($xmlStr);

			$format  = null;
			$entries = array();
			$rootNode = $xml->getName();

			// atom
			if ($rootNode == 'feed') {
				$format = 'atom';

				foreach($xml->entry as $entryElm) {
					$entry = array();
					$entry['title'] = (string)$entryElm->title;

					if (isset($entryElm->published)) {
						$dateStr = (string)$entryElm->published;
					} elseif(isset($entryElm->issued)) {
						$dateStr = (string)$entryElm->issued;
					} elseif(isset($entryElm->updated)) {
						$dateStr = (string)$entryElm->updated;
					} else {
						debug('日付がありません');
						debug($xmlStr);
					}

					$entry['published'] = date('Y-m-d H:i:s', strtotime($dateStr));

					$links = $entryElm->link[0];
					$atts_object  = $links->attributes();
					$atts_array   = (array) $atts_object; //- typecast to an array
					$entry['url'] = $atts_array['@attributes']['href'];

					$entries[] = $entry;
				}
			// RSS2.0
			} elseif($rootNode == 'rss') {
				$format = 'RSS 2.0';

				foreach($xml->channel->item as $entryElm) {
					$entry = array();
					$entry['title'] = (string)$entryElm->title;
					$dateStr = (string)$entryElm->pubDate;
					$entry['published'] = date('Y-m-d H:i:s', strtotime($dateStr));
					$entry['url'] = (string)$entryElm->link;
					$entries[] = $entry;
				}
			// RSS 1.0
			} elseif($rootNode == 'RDF') {
				$format = 'RSS 1.0';
				//debug($xmlStr);
				foreach($xml->item as $entryElm) {
					$dc = $entryElm->children('http://purl.org/dc/elements/1.1/');
					//debug($dc);
					$entry = array();
					$entry['title'] = (string)$entryElm->title;
					$dateStr = (string)$dc->date;
					$entry['published'] = date('Y-m-d H:i:s', strtotime($dateStr));
					$entry['url'] = (string)$entryElm->link;
					$entries[] = $entry;
				}
			}

			$entriesOfSites[] = $entries;
		}

		return $entriesOfSites;
	}


// RSSの処理がボトルネック
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
$beforeTs = time();
		// HTTPで並列にRSSフィードを取得
		$xmlRow = $this->Curl->getContents($feedUrls);
$accessTime = time() - $beforeTs;
debug("HTTPアクセス時間：{$accessTime}秒");
		$parsedFeeds = array();
		foreach ($xmlRow as $j => $xml) {
// 実行時間短縮用に一時的にコメントアウト
$entries = array();

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
				//if (strpos($entries[$i]['title'], '&#') !== false) {
					//debug('&#あり');
					//continue;
				//}

// 実行時間短縮用に一時的にコメントアウト
				// &amp;を&に置き換え
				$entries[$i]['title'] = htmlspecialchars_decode($entries[$i]['title'], ENT_QUOTES);
				$entries[$i]['title'] = htmlspecialchars_decode($entries[$i]['title'], ENT_QUOTES);

				//$entries[$i]['description'] = str_replace('&amp;', '&', $entries[$i]['description']);
			}

			array_push($parsedFeeds, $entries);
		}
$execTime = time() - $beforeTs;
debug("フィードの処理時間：{$execTime}秒");

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
		//$livedoorRssSuffix = 'index.rdf';
		$livedoorRssSuffix = 'atom.xml';
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
