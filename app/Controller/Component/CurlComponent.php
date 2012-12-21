<?php
/**
 * cURLでWEB上のデータを取得するコンポーネント
 * 並列にアクセス可能
 *
 * エラー時の処理
 * ・配列内の要素にfalseが格納される
 *
 */
class CurlComponent extends Component {

	/**
	 * 一度に並列にアクセスする件数
	 *
	 * @var int
	 */
	private $reqCountOnce = 100;

	/**
	 * タイムアウトの秒数
	 *
	 * @var int
	 */
	private $timeOut = 5;

	/**
	 * キャッシュを使わない場合はtrue
	 *
	 * @var bool
	 */
	private $freshConnect = false;


	/**
	 * 実行時間測定用
	 *
	 * @var int
	 */
	private $beforeTs;


	/**
	 * (non-PHPdoc)
	 * @see Component::startup()
	 */
	public function initialize(Controller $controller) {
		parent::initialize($controller);

		//$this->beforeTs = time();
	}



	/**
	 * 指定したURLのコンテンツ（HTML）を取得
	 *
	 * @param  string $url
	 * @return string $html 取得できないときはfalse
	 */
	public function getContent($url) {
		$ch = curl_init($url);
		// cURLオプション
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, $this->freshConnect);
		curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 43200);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    // データを文字列で取得
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);	   // リダイレクト先のコンテンツを取得
		curl_setopt($ch, CURLOPT_MAXREDIRS, 4);			   // リダイレクトを受け入れる回数
		curl_setopt($ch, CURLOPT_FAILONERROR, true);       // 400以上のエラーが返ってきた場合は処理を中断
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeOut); // タイムアウト秒数
		// 実行
		$html  = curl_exec($ch);
		$error = curl_error($ch);
		curl_close($ch);

		// エラーがあればfalseを返す
		if($error != '') {
			debug("取得に失敗しました: {$url}\n{$error}");
			CakeLog::warning("取得に失敗しました: {$url}");

			return false;
		}

		return $html;
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

//debug(time());
		// 引数が単一のURLであれば別のメソッドを使用
		if ( !is_array($urls)) {
			return $this->getContent($url);
		}

		$allContents = array();
		$reqUrls = array();
		// リクエスト用のURLの配列を作成
		foreach ($urls as $i => $url) {

			array_push($reqUrls, $url);

			if ($this->reqCountOnce <= count($reqUrls) || $i + 1 == count($urls)) {
$beforeTs = time();
				$contents = $this->getContentsAtOnce($reqUrls);
$execTime = time() - $beforeTs;
debug("1回のアクセスの処理時間：{$execTime}秒");

				$allContents = array_merge($allContents, $contents);

				$reqUrls = array();
			}
		}

//debug(time());
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
			curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 43200);
			curl_setopt($ch, CURLOPT_FRESH_CONNECT, $this->freshConnect); // キャッシュ
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
				CakeLog::warning("取得に失敗しました: {$urls[$i]}\n{$error}");
			}

			curl_multi_remove_handle($mh, $ch);
			curl_close($ch);
		}
		curl_multi_close($mh);

		return $contents;
	}

	/**
	 * 複数のURLのHTTPヘッダを取得
	 *
	 * $this->reqCountOnceの件数分ずつリクエスト
	 *
	 * @param  array $urls 	  URLの配列
	 * @return array $allHeaders 文字列形式のコンテンツの配列
	 */
	public function getHeaders($urls) {
		$allHeaders = array();

		$reqUrls = array();
		// リクエスト用のURLの配列を作成
		foreach ($urls as $i => $url) {
			array_push($reqUrls, $url);

			if ($this->reqCountOnce <= count($reqUrls) || $i + 1 == count($urls)) {
				$contents = $this->getHeadersOnce($reqUrls);
				$allHeaders = array_merge($allHeaders, $contents);

				$reqUrls = array();
			}
		}

		return $allHeaders;
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
				CakeLog::warning("取得に失敗しました: {$urls[$i]}\n{$error}");
			}

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


	public function setReqCountOnce($count) {
		$this->reqCountOnce = $count;
	}

	public function setTimeOut($timeOut) {
		$this->timeOut = $timeOut;
	}

	public function setFreshConnect($freshConnect) {
		$this->freshConnect = $freshConnect;
	}

}