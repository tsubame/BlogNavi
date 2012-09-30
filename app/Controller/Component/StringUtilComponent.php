<?php
App::uses('HttpSocket', 'Network/Http');

/**
 * 文字列処理関連のコンポーネント
 *
 * ※エラー時の動作も書く
 *
 */
class StringUtilComponent extends Component {

	/**
	 * テキストからURLを抜き出す
	 *
	 * ?以降のパラメータを含める場合は引数の$includeParamをtrueに
	 *
	 * @param  string  $text
	 * @param  boolean $includeParam true ?以降を含める
	 * @return string  URL
	 */
	private function pickUpUrl($text, $includeParam = false) {
		// ?以降を含めない場合
		if ($includeParam == false) {
			$pattern = '/^http:\/\/[\w\.\-\/_=]+/';
		} else {
			$pattern = '/^http:\/\/[\w\.\-\/_=?&@:]+$/';
		}

		if (preg_match($pattern, $text, $matches)) {
			return $matches[0];
		}

		return null;
	}

}