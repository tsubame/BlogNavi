<?php
App::uses('HttpSocket', 'Network/Http');

/**
 * HTTP関連のコンポーネント
 *
 * ※エラー時の動作も書く
 *
 */
class HttpUtilComponent extends Component {

	//public $components = array("Debug", "String");

	/**
	 * URLを受け取ってWebページのタイトルを返す。
	 *
	 * URLにHTTPで接続してタイトルと文字コードを取得。
	 * タイトルを当該の文字コードでエンコードして返す。
	 *
	 * @param  String $url   URL
	 * @return String $title Webページのタイトル 取得できなければnullを返す
	 */
	public function getSiteName($url) {
		// URLをオープン
		if (! @$fp = fopen($url, 'r')) {
			return null;
		}
		// HTMLを1行ずつ読み出す
		$html = null;
		while ($line = fgets($fp)) {
			$html .= $line;
			// 文字コードを取得 例： Content-Type" content="text/html; charset=EUC-JP">
			if (preg_match('/(charset|CHARSET)[^\\w]*([\\w\\-]+)/s', $html, $matches)) {
				$charset = $matches[2];
			}
			// タイトルを取得
			if (preg_match('/<(title|TITLE)>(.+?)<\\/(title|TITLE)>/s', $html, $matches)) {
				$siteTitle = $matches[2];
			}
			// 文字コードとタイトルがともに取得できていればタイトルをエンコードして返す
			if (! empty($charset) & !empty($siteTitle) ) {
				fclose($fp);
				$siteTitle = mb_convert_encoding($siteTitle, 'UTF-8', $charset);
				return $siteTitle ;
			}
		} // end while
		fclose($fp);

		return null;
	}

// 未完全
// バグ修正の必要あり
	/**
	 * URLを受け取ってRSSのフィードURLを返す。
	 *
	 * livedoor、fc2などURLのドメイン名からブログサービス名が判別できる場合は
	 * 各ブログサービスに対応したフィードURLを返す。
	 * 判別できなければURLにHTTPで接続して<link>タグからフィードURLを取得。
	 *
	 * @param  String url
	 * @return String feedUrl 取得できなければnull
	 */
	public function getFeedUrl($url) {
		// URLのドメイン名からフィードURLを取得
		$feedUrl = $this->getFeedUrlFromUrl($url);
		if ($feedUrl != false) {
			return $feedUrl;
		}
		// URLをオープン
		$fp = fopen($url, 'r');
		// HTMLを1行ずつ読み出す
		$html = null;
		while ($line = fgets($fp)) {
			$html .= $line;
			// フィードURLを表記しているタグを取得 <link rel="alternate" type="application/rss+xml" title="RSS" href="http://d.hatena.ne.jp/nunnnunn/rss">
			if (preg_match('/<(link|LINK)([^>]|\n)*?(alternate|ALTERNATE)([^>]|\n)*?(rss|xml)[^>]*?>/s', $html, $matchTags)) {
				// 結果があればURL部分を取得
// href="/jp/feeds/news?fmt=atom" のように書いている場合は取得できない
// ヘッダに書いてない場合は取得できない
				if (preg_match('/(http:\\/\\/)[\\w\\/\\.\\-\\?\\=\\&]+/s', $matchTags[0], $matchURLs)) {
					fclose($fp);
					return $matchURLs[0];
				}
				if (preg_match('/(href|HREF)[^\\w\\/]+([\\w\\/\\.\\-\\?\\=\\&]+)/s', $matchTags[0], $matchURLs)) {
					fclose($fp);
					return $url . $matchURLs[2];
				}
			}
		} // end while
		fclose($fp);

		return null;
	}

	/**
	 * URLの文字列を見てRSSのフィードURLを取得する
	 *
	 * 取得できないときはfalseを返す
	 *
	 * @param  String url
	 * @return String feedUrl
	 */
	private function getFeedUrlFromUrl($url) {
		// 受け取ったURLがフィードURLならそのまま返す
		if (strpos($url, ".rdf") !== false || strpos($url, "?xml") !== false || strpos($url, ".xml") !== false) {
			return $url;
		}
		// ライブドアブログの場合
		if (strpos($url, "livedoor") !== false) {
			return $url.LIVEDOOR_URL_SUFFIX;
		}
		// fc2ブログ
		if (strpos($url, "fc2") !== false) {
			return $url.FC2_URL_SUFFIX;
		}

		return false;
	}

	/**
	 * 短縮URLを展開
	 *
	 * HttpヘッダのLocationを取得し、
	 * Locationが複数ある場合は最後のURLを返す
	 *
	 * 文字数が30文字以上、またはLocationがなければ元のURLを返す
	 *
	 * @param  String $url
	 * @return String 展開後のURL エラー時はfalse
	 */
	public function expandUrl($url) {
		if (is_null($url) || $url == '') {
			return false;
		}
		if (30 < strlen($url)) {
			return $url;
		}
		// URLのHTTPヘッダを取得
		try {
			$header = get_headers($url, 1);
		} catch (Exception $e) {
			debug($e->getMessage());
			return false;
		}
		// HTTPヘッダのLocationが複数ある場合は最後のURLを返す
		if (isset($header['Location'])){
			if (is_array($header['Location'])) {
				return end($header['Location']);
			}

			return $header['Location'];
		}

		return $url;
	}
}