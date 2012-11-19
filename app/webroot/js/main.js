/**
 * http://kuroneko.info 用のJavascriptファイル
 * jQueryを使用
 *
 */

/*==========================================================
  変数の設定 切り替え可能な項目
===========================================================*/

// ページ読み込み時に何件の画像を読み込むか
var preloadImg = 0;


//===========================================================
//jqueryイベント処理 ページ読み込み後
//===========================================================

/**
 * jQuery readyイベント
 */
$(function() {

	var siteId = null;

	/**
	 * サイト更新用のダイアログを開く
	 */
	$(".editFormOpenButton").click(function() {
		// サイトIDを収得
		siteId = $(this).attr("name");
		// ダイアログオープン
		$("#siteEditDialog"+siteId).show();
	});

	/**
	 * サイト更新用のダイアログを開く
	 */
	$(".closeButton").click(function() {
		// ダイアログクローズ
		$(".dialog").hide();
	});

	/**
	 * サイト更新ボタンを押した時
	 */
	$("input.editButton").click(function() {

		// フォームのデータを受け取る
		var id   = siteId;
		var name = $("tr#tr" + siteId + " input.name").val();
		var url  = $("tr#tr" + siteId + " input.url").val();
		var feed_url  =$("tr#tr" + siteId + " input.feed_url").val();
		var category_id  =$("tr#tr" + siteId + " .category_id").get(0).selectedIndex + 1;

		var data = {
				"id": id,
				"name": name,
				"feed_url": feed_url,
				"url": url,
				"category_id": category_id
			};

		// POSTでデータ送信
		$.post("edit", data);

	});

	/**
	 * サイト登録ボタンを押した時
	 */
	$("input.registerButton").click(function() {

		// サイトIDを収得
		var siteId = $(this).attr("name");
		// フォームのデータを受け取る
		var category_id  =$("tr#tr" + siteId + " .category_id").get(0).selectedIndex + 1;

		var data = {
				"id": siteId,
				"is_registered": true,
				"category_id": category_id
			};

		// POSTでデータ送信
		$.post("edit", data);
		// 行を削除
		$("tr#tr" + siteId).hide();
	});

	/**
	 * 削除ボタンを押した時
	 */
	$("input.deleteButton").click(function() {

		// サイトIDを収得
		siteId = $(this).attr("name");

		var data = {
				"id": siteId
			};

		//jQuery("tr#tr" + siteId).hide();
		$("tr#tr" + siteId).hide();

		// POSTでデータ送信
		$.post("delete", data);
	});

});



/**
 * デバッグ用 firebugにメッセージを出力
 *
 * @param String msg 出力メッセージ
 */
function info(msg) {
	// ブラウザがmozilla系でなければ終了
	if ( !$.browser.mozilla ) {
		return;
	}

	console.info(msg);
}
