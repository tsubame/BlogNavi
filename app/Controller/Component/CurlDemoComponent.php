<?php
/**
 * cURLのデモ
 *
 */
class CurlDemoComponent extends Component {



	/**
	 *
	 *
	 */
	public function demo() {

		$url = 'http://yahoo.co.jp/';

		$chYahoo = curl_init($url);
		curl_setopt($chYahoo, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($chYahoo, CURLOPT_HEADER, true);
		curl_setopt($chYahoo, CURLOPT_NOBODY, true);

		$result = curl_exec($chYahoo);
		curl_close($chYahoo);

		debug($result);
		echo $result;
	}

	/**
	 *
	 */
	public function multiDemo() {

		$urls = array(
				'http://pawaplog.blog2.fc2.com/',
				'http://blog.livedoor.jp/nanjstu/',
				'http://bit.ly/PjOJ5C');
		$mh = curl_multi_init();


		$channels = array();
		// URLの件数ループ
		foreach ($urls as $i => $url) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_multi_add_handle($mh, $ch);

			array_push($channels, $ch);
		}

		// ここの処理がよくわからない
		do {
			// running?
			curl_multi_exec($mh, $running);
		} while ($running);

		$results = array();

		foreach ($channels as $i => $ch) {
			$results[$i] =  curl_multi_getcontent($ch);
			curl_multi_remove_handle($mh, $ch);
			curl_close($ch);

			debug($results[$i]);
			if (preg_match('/Location:[\s]*([^\n\s?]+)/', $results[$i], $matches)) {
				echo $matches[1];
			}
		}

		curl_multi_close($mh);
	}

	/**
	 * 短縮URLを展開
	 *
	 *
	 * @param  $orgUrls
	 * @return $longUrls
	 */
	public function expandUrls($orgUrls) {
		$mh = curl_multi_init();

		$channels = array();
		// URLの件数ループ
		foreach ($orgUrls as $i => $url) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			// リダイレクト先のコンテンツを取得
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			// リダイレクトを受け入れる回数
			curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
			curl_multi_add_handle($mh, $ch);

			array_push($channels, $ch);
		}
		// 実行
		do {
			// running?
			curl_multi_exec($mh, $running);
		} while ($running);

		$longUrls = array();
		foreach ($channels as $i => $ch) {
			$result =  curl_multi_getcontent($ch);

			curl_multi_remove_handle($mh, $ch);
			curl_close($ch);
debug($result);
			if (preg_match('/Location:[\s]*([^\n\s]+)/', $result, $matches)) {
				$longUrls[$i] = $matches[1];
			} else {
				$longUrls[$i] = $orgUrls[$i];
			}
		}

		return $longUrls;
	}



}