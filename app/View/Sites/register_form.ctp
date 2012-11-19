<div class = "formArea">
	<fieldset>
<?php
	//$categories = array(1 => 'ニュース', 2 => '2ch', 3 => 'ブログ');

	echo $this->Form->create(array('action' => 'register'));
	//echo $this->Form->input('Site.url', array('type' => 'text', 'default' => ' '));
	?>

	<div class="input text"><label for="SiteUrl">Url</label><input name="data[Site][url]" value="" id="SiteUrl" type="text"></div>
	<?= $this->Form->input('Site.category_id', array('type' => 'select', 'options' => $categories, 'selected' => 1)); ?>
	<br />
	<div class="input text"><label for="SiteName">サイト名</label><input name="data[Site][name]" value="" id="SiteName" type="text"></div>
	<div class="input text"><label for="SiteFeedUrl">RSS URL</label><input name="data[Site][feed_url]" value="" id="SiteFeedUrl" type="text"></div>
<?php
	echo $this->Form->submit('登録');
	echo $this->Form->end();
?>
	</fieldset>
</div>