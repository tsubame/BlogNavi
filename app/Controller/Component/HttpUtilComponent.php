<?php
App::uses('HttpSocket', 'Network/Http');

/**
 * HTTP関連のコンポーネント
 *
 * ※エラー時の動作も書く
 *
 */
class HttpUtilComponent extends Component {

	// ライブドアブログのRSSフィードのURLの末尾
	const LIVEDOOR_URL_SUFFIX = 'index.rdf';
	// FC2のRSSフィードのURLの末尾
	const FC2_URL_SUFFIX = '?xml';
	// はてなのRSSフィードのURLの末尾
	const HATENA_URL_SUFFIX = 'index.rdf';
	// アメブロのRSSフィードのURLの末尾
	const AMEBA_URL_SUFFIX = 'index.rdf';

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
	 * @return String feedUrl 取得できなければfalse
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

// 外に出す
$feedTagPattern = '/<(link|LINK)([^>]|\n)*?(alternate|ALTERNATE)([^>]|\n)*?(rss|xml)[^>]*?>/s';
$feedUrlPattern = '/(http:\\/\\/)[\\w\\/\\.\\-\\?\\=\\&]+/s';
$feedHrefPattern = '/(href|HREF)[^\\w\\/]+([\\w\\/\\.\\-\\?\\=\\&]+)/s';

		while ($line = fgets($fp)) {
			$html .= $line;
			// フィードURLを表記しているタグを取得 <link rel="alternate" type="application/rss+xml" title="RSS" href="http://d.hatena.ne.jp/nunnnunn/rss">
			if (preg_match($feedTagPattern, $html, $matchTags)) {
				// 結果があればURL部分を取得
				if (preg_match($feedUrlPattern, $matchTags[0], $matchURLs)) {
					fclose($fp);
					return $matchURLs[0];
				}
				if (preg_match($feedHrefPattern, $matchTags[0], $matchURLs)) {
					// URLから最初の/までを取得
					if (preg_match('/(http:\/\/[\w\.\-_=]+)\//', $url, $matches)) {
						$url = $matches[1];
					}

					return $url . $matchURLs[2];
				}
			}
		} // end while
		fclose($fp);

		return false;
	}

	/**
	 * URLを受け取ってRSSのフィードURLを返す。
	 *
	 * curlを使用
	 *
	 * @param  String url
	 * @return String 取得できなければfalse
	 */
	public function getFeedUrlbyCurl($url) {

		//$url = "http://www.example.com";
		// 初期化
		$html = $this->getContents($url);
// 外に出す
$feedTagPattern = '/<(link|LINK)([^>]|\n)*?(alternate|ALTERNATE)([^>]|\n)*?(rss|xml)[^>]*?>/s';
$feedUrlPattern = '/(http:\\/\\/)[\\w\\/\\.\\-\\?\\=\\&]+/s';
$feedHrefPattern = '/(href|HREF)[^\\w\\/]+([\\w\\/\\.\\-\\?\\=\\&]+)/s';

		if (preg_match($feedTagPattern, $html, $matchTags)) {
			if (preg_match($feedUrlPattern, $matchTags[0], $matchURLs)) {
				return $matchURLs[0];
			}

			if (preg_match($feedHrefPattern, $matchTags[0], $matchURLs)) {
				// URLから最初の/までを取得
				if (preg_match('/(http:\/\/[\w\.\-_=]+)\//', $url, $matches)) {
					$url = $matches[1];
				}

				return $url . $matchURLs[2];
			}
		}

		return false;
	}

	/**
	 * 指定したURLのコンテンツ（HTML）を取得
	 *
	 *
	 * @param  string $url
	 * @return string $html 取得できないときはfalse
	 */
	public function getContents($url) {
		$timeOut = 10;

		$ch = curl_init();
		// オプション
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// リダイレクト先のコンテンツを取得
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		// リダイレクトを受け入れる回数
		curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
		// タイムアウトの設定
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);

		$html = curl_exec($ch); // 3. 実行してデータを得る

		$error = curl_error($ch);

		// エラーがなければコンテンツ内容、HTTPコードの取得
		if($error != '') {
			debug("取得に失敗しました: {$url}");

			return false;
		}

		curl_close($ch);

		return $html;
	}

	/**
	 * URLの文字列を見てRSSのフィードURLを取得する
	 *
	 * 取得できないときはfalseを返す
	 *
	 * @param  String url
	 * @return String feedUrl
	 */
	protected function getFeedUrlFromUrl($url) {


		// 受け取ったURLがフィードURLならそのまま返す
		if (strpos($url, ".rdf") !== false || strpos($url, "?xml") !== false || strpos($url, ".xml") !== false) {
			return $url;
		}
		// ライブドアブログの場合
		if (strpos($url, "livedoor") !== false) {
			return $url . self::LIVEDOOR_URL_SUFFIX;
		}
		// fc2ブログ
		if (strpos($url, "fc2") !== false) {
			return $url . self::FC2_URL_SUFFIX;
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