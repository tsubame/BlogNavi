<?php
/**
 * cURLで並列にデータを取得するコンポーネント
 *
 * エラー時の処理
 * ・配列内の要素にfalseが格納される
 *
 */
class CurlMultiComponent extends Component {

	// 一度に検索する件数
	private $reqCountOnce = 30;
	// タイムアウトの秒数
	private $timeOut = 10;

	/**
	 * 複数のURLのHTTPボディ（コンテンツ）を取得
	 *
	 * $this->reqCountOnceの件数分ずつリクエスト
	 *
	 * @param  array $urls 	  URLの配列
	 * @return array $allContents 文字列形式のコンテンツの配列
	 */
	public function getHeaders($urls) {
		$allContents = array();

		$reqUrls = array();
		// リクエスト用のURLの配列を作成
		foreach ($urls as $i => $url) {
			array_push($reqUrls, $url);

			if ($this->reqCountOnce <= count($reqUrls) || $i + 1 == count($urls)) {
				$contents = $this->getHeadersOnce($reqUrls);
				$allContents = array_merge($allContents, $contents);

				$reqUrls = array();
			}
		}

		return $allContents;
	}

	/**
	 * 複数のURLのHTTPヘッダを取得する。
	 * リダイレクトされた場合はアクセスしたURLすべてのHTTPヘッダが取得される
	 *
	 * @param  array $urls    URLの配列
	 * @return array $headers 文字列形式のHTTPヘッダの配列
	 */
	public function getHeadersOnce($urls) {
		$mh = curl_multi_init();
		$channels = array();

		// URLの件数チャンネル作成
		foreach ($urls as $url) {
			$ch = curl_init($url);
			// cURLオプション
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    // データを文字列で取得
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);    // リダイレクト先のコンテンツを取得
			curl_setopt($ch, CURLOPT_MAXREDIRS, 4);			   // リダイレクトを受け入れる回数
			curl_setopt($ch, CURLOPT_FAILONERROR, true);       // 400以上のエラーが返ってきた場合は処理を中断
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeOut); // タイムアウト秒数
			curl_setopt($ch, CURLOPT_HEADER, true);		       // HTTP Headerを出力
			curl_setopt($ch, CURLOPT_NOBODY, true);            // HTTP Bodyを出力しない

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
			$http_code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			// エラーチェック
			$error = curl_error($ch);
			if($error != '') {
				$headers[$i] = false;
				debug("取得に失敗しました: {$urls[$i]}\n{$error}");
			}

			curl_multi_remove_handle($mh, $ch);
			curl_close($ch);
		}
		curl_multi_close($mh);

		return $headers;
	}

	/**
	 * 複数のURLのHTTPボディ（コンテンツ）を取得
	 *
	 * $this->reqCountOnceの件数分ずつリクエスト
	 *
	 * @param  array $urls 	  URLの配列
	 * @return array $allContents 文字列形式のコンテンツの配列
	 */
	public function getContents($urls) {
		$allContents = array();

		$reqUrls = array();
		// リクエスト用のURLの配列を作成
		foreach ($urls as $i => $url) {
			array_push($reqUrls, $url);

			if ($this->reqCountOnce <= count($reqUrls) || $i + 1 == count($urls)) {
				$contents = $this->getContentsAtOnce($reqUrls);
				$allContents = array_merge($allContents, $contents);

				$reqUrls = array();
			}
		}

		return $allContents;
	}

	/**
	 * 複数のURLのHTTPボディ（コンテンツ）を取得
	 *
	 * 取得できない場合は配列にfalseを入れる
	 *
	 * @param  array $urls 	  URLの配列
	 * @return array $contents 文字列形式のコンテンツの配列
	 */
	public function getContentsAtOnce($urls) {

		$mh = curl_multi_init();
		$channels = array();

		// URLの件数チャンネル作成
		foreach ($urls as $url) {
			$ch = curl_init($url);
			// cURLオプション
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    // データを文字列で取得
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);	   // リダイレクト先のコンテンツを取得
			curl_setopt($ch, CURLOPT_MAXREDIRS, 4);			   // リダイレクトを受け入れる回数
			curl_setopt($ch, CURLOPT_FAILONERROR, true);       // 400以上のエラーが返ってきた場合は処理を中断
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeOut); // タイムアウト秒数

			curl_multi_add_handle($mh, $ch);
			array_push($channels, $ch);
		}
		// 処理実行
		do {
			curl_multi_exec($mh, $running);
		} while ($running);

		$contents = array();
		// 各チャンネルからデータ取得
		foreach ($channels as $i => $ch) {
			$contents[$i] =  curl_multi_getcontent($ch);
			$http_code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			// エラーチェック
			$error = curl_error($ch);
			if($error != '') {
				$contents[$i] = false;
				debug("取得に失敗しました: {$urls[$i]}\n{$error}");
			}

			curl_multi_remove_handle($mh, $ch);
			curl_close($ch);
		}
		curl_multi_close($mh);

		return $contents;
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


	public function setreqCountOnce($count) {
		$this->reqCountOnce = $count;
	}

	public function setTimeOut($timeOut) {
		$this->timeOut = $timeOut;
	}


}