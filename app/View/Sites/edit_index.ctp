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
		<td class = "catButton">
			<input type = "button" value = "カテゴリ変更" class = "button" name = "<?= $site['id'] ?>" />
		</td>
		<td class = "editButton">
			<input type = "button" value = "編集" class = "button editFormOpenButton" name = "<?= $site['id'] ?>" />
		</td>
		<td class = "deleteButton">
			<input type = "button" value = "削除" class = "button deleteButton" name = "<?= $site['id'] ?>"  />

			<fieldset class = "siteEditDialog dialog" id = "siteEditDialog<?= $site['id'] ?>">
				<?= $this->Form->input('Site.name', array('default' => $site['name'], 'class' => 'name')); ?>
				<?= $this->Form->input('Site.url', array('default' => $site['url'], 'class' => 'url')); ?>
				<?= $this->Form->input('Site.feed_url', array('default' => $site['feed_url'], 'class' => 'feed_url')); ?>
				<?= $this->Form->input('Site.category_id', array('type' => 'select', 'options' => $categories, 'selected' => $site['category_id'], 'class' => 'category_id')); ?>
				<p class = "submit">
					<input type = "button" value = "更新" class = "closeButton editButton" name = "<?= $site['id'] ?>" />
				</p>
			</fieldset>
		</td>
	</tr>
	<?php } ?>
</table>
