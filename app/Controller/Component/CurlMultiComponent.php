<?php
/**
 * cURLで並列にデータを取得するコンポーネント
 *
 * 必要：エラー処理
 *
 */
class CurlMultiComponent extends Component {



// 一度に取得する件数も設定する必要あり

	/**
	 * 複数のURLのHTTPヘッダを取得する。
	 * リダイレクトされた場合はアクセスしたURLすべてのHTTPヘッダが取得される
	 *
	 * @param  array $urls    URLの配列
	 * @return array $headers 文字列形式のHTTPヘッダの配列
	 */
	public function getHeaders($urls) {
		$mh = curl_multi_init();
		$channels = array();

		// URLの件数チャンネル作成
		foreach ($urls as $url) {
			$ch = curl_init($url);
			// cURLオプション
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // データを文字列で取得
			curl_setopt($ch, CURLOPT_HEADER, true);			// HTTP Headerを出力
			curl_setopt($ch, CURLOPT_NOBODY, true);			// HTTP Bodyを出力しない
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);	// リダイレクト先のコンテンツを取得
			curl_setopt($ch, CURLOPT_MAXREDIRS, 2);			// リダイレクトを受け入れる回数
			curl_multi_add_handle($mh, $ch);

			array_push($channels, $ch);
		}
		// 処理実行
		do {
			curl_multi_exec($mh, $running);
		} while ($running);

		$headers = array();
		// 各チャンネルからデータ取得
		foreach ($channels as $i => $ch) {
			$headers[$i] = curl_multi_getcontent($ch);
			curl_multi_remove_handle($mh, $ch);
			curl_close($ch);
		}
		curl_multi_close($mh);

		return $headers;
	}

	/**
	 * 複数のURLのHTTPボディ（コンテンツ）を取得
	 *
	 * @param  array $urls 	  URLの配列
	 * @return array $headers 文字列形式のコンテンツの配列
	 */
	public function getContents($urls) {

		$mh = curl_multi_init();
		$channels = array();

		// URLの件数チャンネル作成
		foreach ($urls as $url) {
			$ch = curl_init($url);
			// cURLオプション
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // データを文字列で取得
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);	// リダイレクト先のコンテンツを取得
			curl_setopt($ch, CURLOPT_MAXREDIRS, 4);			// リダイレクトを受け入れる回数

			curl_multi_add_handle($mh, $ch);
			array_push($channels, $ch);
		}
		// 処理実行
		do {
			curl_multi_exec($mh, $running);
		} while ($running);

		$headers = array();
		// 各チャンネルからデータ取得
		foreach ($channels as $i => $ch) {
			$headers[$i] =  curl_multi_getcontent($ch);
			curl_multi_remove_handle($mh, $ch);
			curl_close($ch);
		}
		curl_multi_close($mh);

		return $headers;
	}

	/**
	 * 短縮URLをまとめて展開
	 *
	 * 各URLのヘッダを取得してLocationフィールドのURLを抜き出す。
	 * 複数回リダイレクトされた場合は$headerには複数のHTTPヘッダが含まれるので、
	 * 最後のLocationを取得する。
	 *
	 * @param  array $orgUrls  URLの配列
	 * @return array $longUrls 展開後のURLの配列
	 */
	public function expandUrls($orgUrls) {
		$longUrls = array();
		// 各URLのHTTPヘッダを取得
		$headers = $this->getHeaders($orgUrls);

		foreach ($headers as $i => $header) {
			// LocationフィールドのURLを取得S
			if (preg_match_all('/Location:[\s]*([^\n\s?]+)/', $header, $matches)) {
				// Locationが複数ある場合は一番最後を取得
				if (is_array($matches[1])) {
					$longUrl = end($matches[1]);
				} else {
					$longUrl = $matches[1];
				}

				array_push($longUrls, $longUrl);
			} else {
				array_push($longUrls, $orgUrls[$i]);
			}
		}

		return $longUrls;
	}



}