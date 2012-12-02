サイトのリスト

<br />
<?= $this->Html->link('ブログランキングからサイトを登録', array('controller' => 'sites','action' => 'registerFromRank')) ?>　　
<?= $this->Html->link('サイトをファイルから登録', array('controller' => 'sites','action' => 'registerFromFile')) ?>　　
<?= $this->Html->link('未登録サイトをすべて登録', array('controller' => 'sites','action' => 'registerAll')) ?>　　
<?= $this->Html->link('Sナビからサイトを登録', array('controller' => 'sites', 'action' => 'registerFromSNavi')) ?>

<table class = "site">
	<?php

// 外に出す
//$categories = array(1 => 'ニュース', 2 => '2ch', 3 => 'ブログ', 4 => 'ブログ（有名人）');

	foreach ($sites as $site) {
	 ?>
	<tr id = "tr<?= $site['id'] ?>">
		<td class = "name">
			<a href = "<?= $site['url'] ?>" target = "_blank"><?= $site['name'] ?></a>
		</td>
		<td class = "feed">
			<a href = "<?= $site['feed_url'] ?>" target = "_blank">RSS</a>
		</td>
		<td class = "register_from">
			<?= $site['registered_from'] ?>
		</td>
		<td class = "category">
			<?= $this->Form->input('Site.category_id', array('type' => 'select', 'label' => ' ', 'options' => $categories, 'selected' => $site['category_id'], 'class' => 'category_id')); ?>
		</td>
		<td class = "editButton">
			<input type = "button" value = "登録" class = "button registerButton" name = "<?= $site['id'] ?>" />
		</td>
		<td class = "deleteButton">
			<input type = "button" value = "削除" class = "button deleteButton" name = "<?= $site['id'] ?>"  />
		</td>
	</tr>
	<?php } ?>
</table>
