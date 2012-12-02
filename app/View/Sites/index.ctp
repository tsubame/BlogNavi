<?php
/**
 * サイト一覧 ユーザ向け
 */
?>

サイトのリスト

<table class = "site">
	<?php

// 外に出す
//$categories = array(1 => 'ニュース', 2 => '2ch', 3 => 'ブログ');

	foreach ($sites as $site) {
	 ?>
	<tr id = "tr<?= $site['id'] ?>">
		<td class = "name">
			<a href = "<?= $site['url'] ?>" target = "_blank"><?= $site['name'] ?></a>
		</td>
		<td class = "feed">
			<a href = "<?= $site['feed_url'] ?>" target = "_blank">RSS</a>
		</td>
		<td class = "category">
				<?= $this->Form->input('Site.category_id', array('type' => 'select', 'label' => ' ', 'options' => $categories, 'selected' => $site['category_id'], 'class' => 'category_id')); ?>
		</td>
	</tr>
	<?php } ?>
</table>
