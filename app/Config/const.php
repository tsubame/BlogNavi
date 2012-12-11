<?php

/**
 * 表示する件数
 *
 * @see Article
 * @var int
 */
Configure::write('Article.showCount', 30);
//Configure::read('Article.showCount');

/**
 * 何時間前の記事まで登録するか
 *
 * RSSで取得した記事のうち、この時間以上過去の記事は無視する
 *
 * @see Article
 * @var int
 */
Configure::write('Article.registerPastHourFrom', 24);

/**
 * 記事を自動で削除する際に使用。
 * この日付分前の記事から削除する。
 *
 * 1 → 1日前の記事から削除
 *
 * @see Article::deletePastArticles
 * @var int
 */
Configure::write('Article.deletePastDayFrom', 1);

/**
 * 記事を自動で削除する際に使用。
 * この日数前までの記事を削除する。
 *
 * 50 → 50日前の記事まで削除
 *
 * @see Article::deletePastArticles
 * @var int
 */
Configure::write('Article.deletePastTo', 50);

/**
 * カテゴリー名
 * 添字はカテゴリーID
 *
 * @var array
 */
Configure::write('Category.names', array(
			1 => 'ニュース',
			2 => '2chまとめ',
			3 => 'ブログ',
			4 => 'ブログ（選手）',
			5 => '海外の反応')
		);

/**
 * ニュースサイトのカテゴリID
 *
 * @see SiteRegisterFromRankAction
 * @var int
 */
Configure::write('Category.newsId', 1);

/**
 * ２chまとめブログのカテゴリID
 *
 * @see SiteRegisterFromRankAction
 * @var int
 */
Configure::write('Category.2chId', 2);

/**
 * ブログ（その他）のカテゴリID
 *
 * @see SiteRegisterFromRankAction
 * @var int
 */
Configure::write('Category.blogId', 3);

/**
 * ブログ（選手）のカテゴリID
 *
 * @see SiteRegisterFromRankAction
 * @var int
 */
Configure::write('Category.playerId', 4);

/**
 * ブログ（海外の反応）のカテゴリID
 *
 * @see SiteRegisterFromRankAction
 * @var int
 */
Configure::write('Category.foreignId', 5);


/**
 * サイトを登録する際のファイルのディレクトリ
 *
 * @var string
 */
Configure::write('Site.fileDirPath', WWW_ROOT . 'files' . DS);

/**
 * サイトを登録する際のファイル名
 * 添字はカテゴリーID
 *
 * @var array
 * @see SiteRegisterFromFileAction::exec
 */
Configure::write('Site.fileNames', array(
			1 => 'sites_news.txt',
			2 => 'sites_2ch.txt',
			3 => 'sites_blog.txt',
			4 => 'sites_player.txt',
			5 => 'sites_foreign.txt')
		);

/**
 * 正規表現でのURLのパターン
 *
 * @see SiteRegisterFromFileAction::splitUrlAndSiteName()
 * @var string
 */
Configure::write('urlPattern', '/^http:\/\/[\w\.\-\/_=?&@:]+/');




/**
 * ニュースサイトのファイル名
 *
 * @var string
 */
Configure::write('Site.newsFileName', 'sites_news.txt');
//const FILE_NEWS = 'sites_news.txt';

/**
 * 2chまとめサイトのファイル名
 *
 * @var string
 */
Configure::write('Site.2chFileName', 'sites_2ch.txt');
//const FILE_2CH = 'sites_2ch.txt';

/**
 * ブログサイト（その他）のファイル名
 *
 * @var string
 */
Configure::write('Site.blogFileName', 'sites_blog.txt');
//const FILE_BLOG = 'sites_blog.txt';

/**
 * ブログ（選手）サイトのファイル名
 *
 * @var string
 */
Configure::write('Site.playerFileName', 'sites_player.txt');

/**
 * ブログ（海外の反応）サイトのファイル名
 *
 * @var string
 */
Configure::write('Site.foreignFileName', 'sites_foreign.txt');