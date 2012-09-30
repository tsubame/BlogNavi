<?php
App::uses('HttpSocket', 'Network/Http');
//App::import("Component", "StringUtil");
//App::import("Component", "Debug");

/**
 * 対象URLのHTMLデータを並列に取得するクラス
 *
 * 対象URLにHTTPで接続してHTMLデータを文字列形式で取得する
 * //gzip形式で圧縮されている場合は自動的に展開を実行
 * //エラー時にはnullを返す
 *
 */
class HttpParallelComponent extends Component {

	public $components = null;

	// タイムアウト秒数
	const TIME_OUT = 10;
	// 並列に実行する通信の最大数（デフォルト値）
	const REQ_COUNT_ONCE = 30;

	/**
	 * コンストラクタ
	 */
	public function __construct() {
		/*
		if ( !isset($this->StringUtil) ) {
			$this->StringUtil = new StringUtilComponent();
		}
		if ( !isset($this->Debug) ) {
			$this->Debug = new DebugComponent();
		}
		*/
	}


	/**
	 * 複数のURLからHTMLデータ（ソースコード）を並列に取得する
	 * cURLの並列通信を使用
	 *
	 * @param  array $urls         URLの配列
	 * @param  int   $reqCountOnce 一度に実行する通信の最大数
	 * @return array $contents     取得したHTMLデータの配列
	 */
	public function getDataParallel($urls, $reqCountOnce = self::REQ_COUNT_ONCE) {
		$contents = array();
		$reqUrls  = array();
		// URLの件数分ループ
		foreach ($urls as $i => $url) {
			// リクエスト用の配列にURLを入れる
			array_push($reqUrls, $url);
			// 配列内にURLが一定数溜まるまでスキップ
			if(count($reqUrls) < $reqCountOnce && $i + 1 < count($urls)) {
				continue;
			}
			// cURLで並列通信を実行
			$results = $this->curlMulti($reqUrls);
			// 結果をマージ
			$contents = array_merge($contents, $results);

			$reqUrls  = array();
		}

		return $contents;
	}

	/**
	 * cURL並列通信を実行
	 *
	 * @param  array $urls     URLの配列
	 * @return array $contents 取得したコンテンツの配列
	 */
	private function curlMulti($urls) {
		$connects = array();
		$mh       = curl_multi_init();
		// cURL並列通信の準備
		$this->curlMultiInit($urls, $connects, $mh);
		// cURL並列通信の実行
		$this->curlMultiExec($mh);
		// 実行結果を取得
		$contents = $this->getCurlMultiResult($urls, $connects, $mh);
		// 閉じる
		curl_multi_close($mh);

		return $contents;
	}

	/**
	 * cURL並列通信の準備
	 * 各種パラメータを設定
	 *
	 * @param array  $urls      URLの配列
	 * @param object &$connects cURLコネクション
	 * @param object &$mh       cURLマルチハンドル
	 */
	private function curlMultiInit($urls, &$connects, &$mh) {
		// URLの件数分のコネクションを生成
		foreach ($urls as $i => $url) {
			$connects[$i] = curl_init($url);
			curl_setopt($connects[$i], CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($connects[$i], CURLOPT_FAILONERROR, 1);
			// リダイレクト先のコンテンツを取得
			curl_setopt($connects[$i], CURLOPT_FOLLOWLOCATION, true);
			// リダイレクトを受け入れる回数
			curl_setopt($connects[$i], CURLOPT_MAXREDIRS, 2);


// ヘッダのみを取得
//curl_setopt($connects[$i], CURLOPT_NOBODY, true);


			// SSL証明書を無視
			curl_setopt($connects[$i], CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($connects[$i], CURLOPT_SSL_VERIFYHOST, false);
			// タイムアウトの設定
			if (self::TIME_OUT) {
				curl_setopt($connects[$i], CURLOPT_TIMEOUT, self::TIME_OUT);
			}
			curl_multi_add_handle($mh, $connects[$i]);
		}
	}

	/**
	 * cURL並列通信の実行
	 *
	 * @param object $mh cURLマルチハンドル
	 */
	private function curlMultiExec($mh) {
		$active = null;
		// 一括で通信実行、全て終わるのを待つ

// HTTPヘッダを取得するようにするにはこの辺りを改変？
		do {
			$mrc = curl_multi_exec($mh, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);

		while ($active && $mrc == CURLM_OK) {
			if (curl_multi_select($mh) != -1) {
				do {
					$mrc = curl_multi_exec($mh, $active);
				}
				while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
		}
		// エラー時
		if ($mrc != CURLM_OK) {
			echo "読み込みエラーが発生しました: {$mrc}";
		}
	}

	/**
	 * cURL並列通信での実行結果を取得
	 *
	 * @param  array  $urls      URLの配列
	 * @param  object &$connects cURLコネクション
	 * @param  object &$mh       cURLマルチハンドル
	 * @return array  $contents  取得したコンテンツの配列
	 */
	private function getCurlMultiResult($urls, &$connects, &$mh) {
		$contents = array();
		// 各通信のエラーの有無をチェック
		foreach ($urls as $i => $url) {
			$error = curl_error($connects[$i]);
			//

			// エラーがなければコンテンツ内容、HTTPコードの取得
			if($error == '') {
				$contents[$i] = curl_multi_getcontent($connects[$i]);
				$http_code = curl_getinfo($connects[$i], CURLINFO_HTTP_CODE);
				// HTTPコードが301 or 302の時
				if ($http_code == 301 || $http_code == 302) {
					echo $http_code.$connects[$i];
					print_r(curl_getinfo($connects[$i]));
				}
			} else {
				$contents[$i] = null;
				debug("取得に失敗しました: {$url}");
			}
			curl_multi_remove_handle($mh, $connects[$i]);
			curl_close($connects[$i]);
		}

		return $contents;
	}

}