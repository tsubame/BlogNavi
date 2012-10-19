<?php
//App::import("Component", "HtmlFetcher");
//App::import("Component", "Debug");
/**
 * RSSを取得するコンポーネント
 *
 * // 要：MagpieRssライブラリ
 *
 * 要：SimplePieライブラリ
 *     CurlMultiコンポーネント
 *
 */
class RssFetcherDemoComponent extends Object {

	public  $name       = 'RssFetcher';
	public  $components = array("CurlMulti");

	/**
	 * コンストラクタ
	 *
	 */
	public function __construct() {
		if ( !isset($this->HtmlFetcher) ) {
			$this->HtmlFetcher = new HtmlFetcherComponent();
		}
	}

	/**
	 * RSSフィードを並列に取得
	 * 外部ライブラリ「Magpie」オブジェクトの形式で取得する
	 *
	 * @param  array $feedUrls フィードURLの配列
	 * @return array $magFeeds Magpieオブジェクトの配列
	 */
	public function getFeedParallel($feedUrls) {
		// 並列にRSSフィードを取得
		//$rssFeeds = $this->HtmlFetcher->getDataParallel($feedUrls);
		$magFeeds = array();
		// RSSフィードをMagpieオブジェクトに変換して配列にセット
		foreach ($rssFeeds as $rssFeed) {
			$magRss = $this->getMagpieRss($rssFeed);
			array_push($magFeeds, $magRss);
		}

		return $magFeeds;
	}

	/**
	 * Magpieオブジェクトを体裁を整えて配列に格納
	 * 取得できない項目にはnullを設定
	 *
	 * @param  array $magEntry MagpieRSSで取得したエントリ
	 * @return array $entry    変換後のエントリ
	 * 　　　　　　　　　array("url" => URL, "title" => タイトル, "pubDate" => 発行日時, "contents" => サマリー)
	 */
	public function magpieToArray($magEntry) {
		$entry = array();
		$entry['url'] = $magEntry['link'];
		// タイトルを取得
		if (!empty($magEntry['title'])) {
			$entry['title'] = $magEntry['title'];
		} else {
			$entry['title'] = null;
		}
		// 日付を取得
		$entry['pub_date'] = $this->getMagpieDate($magEntry);

		// サマリーを取得 RSS1.0、2.0、atomで項目が異なる
		if (!empty($magEntry['content'])) {
			$entry['contents'] = $magEntry['content']['encoded'];
		} elseif (!empty($magEntry['atom_content'])) {
			$entry['contents'] = $magEntry['atom_content'];
		} elseif (!empty($magEntry['description'])) {
			$entry['contents'] = $magEntry['description'];
		}

		return $entry;
	}

	/**
	 * MagpieRssエントリの日付をDATETIME形式の文字列で取得
	 * 現在日時より後の日付であれば現在日時に直す
	 *
	 * @param  array  $magEntry MagpieRssのエントリ
	 * @return String $dateTime DATETIME形式の文字列
	 */
	private function getMagpieDate($magEntry) {
		// 日付を取得 RSS1.0、2.0、atomでは日付の項目名が異なる
		if (!empty($magEntry['dc']['date'])) {
			$date = $magEntry['dc']['date'];
		} elseif (!empty($magEntry['pubdate'])) {
			$date = $magEntry['pubdate'];
		} elseif (!empty($magEntry['published'])) {
			$date = $magEntry['published'];
		} elseif (!empty($magEntry['created'])) {
			$date = $magEntry['created'];
		} elseif (!empty($magEntry['modified'])) {
			$date = $magEntry['modified'];
		} else {
			return null;
		}
		// 日付をDATETIME形式の文字列に変換
		$dateTime = date('Y-m-d H:i:s', parse_w3cdtf($date));
		// 現在日時より後の日付であれば現在日時に直す
		if (time() < $this->Date->getTsFromDateTime($dateTime)) {
			$dateTime = date('Y-m-d H:i:s');
		}

		return $dateTime;
	}

	/**
	 * RSSデータをMagpieオブジェクトに変換
	 * 失敗時にはfalseを返す
	 *
	 * @param  String &$rssFeed RSSデータ
	 * @return object $magRss   Magpieオブジェクト
	 */
	private function getMagpieRss(&$rssFeed) {
		if(is_null($rssFeed)) {
			return false;
		}
		// RSSデータをMagpieオブジェクトに変換
		$magRss = new MagpieRSS($rssFeed, 'UTF-8');
		// rsschannelがなければfalseを返す
		if (empty($magRss->channel)) {
			return false;
		}

		return $magRss;
	}


}
