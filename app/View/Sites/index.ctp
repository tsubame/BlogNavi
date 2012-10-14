サイトのリスト

<div class = "formArea">


<?php
	$categories = array(1 => 'ニュース', 2 => '2ch', 3 => 'ブログ');
	foreach ($sites as $site) {
?>
	<fieldset>
	<?php echo $this->Form->create(array('action' => './update')); ?>
		<?php 	echo $this->Form->input('Site.id', array('type' => 'hidden', 'default' => $site['id'])); ?>
		<?php 	echo $this->Form->input('Site.name', array('default' => $site['name'])); ?>
		<?php	echo $this->Form->input('Site.url', array('default' => $site['url'])); ?>
		<?php 	echo $this->Form->input('Site.category_id', array('type' => 'select', 'options' => $categories, 'selected' => $site['category_id'])); ?>
		<?php 	echo $this->Form->submit('更新', array('div' => false)); ?>
	<?php 	echo $this->Form->end(); ?>
	<?php echo $this->Form->create(array('action' => './delete')); ?>
			<?php 	echo $this->Form->input('Site.id', array('type' => 'hidden', 'default' => $site['id'])); ?>
			<?php 	echo $this->Form->submit('削除', array('div' => false)); ?>
	<?php 	echo $this->Form->end(); ?>
	</fieldset>
<?php
	}
?>
</div>