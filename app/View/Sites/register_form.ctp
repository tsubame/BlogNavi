<div class = "formArea">
	<fieldset>
<?php
	$categories = array(1 => 'ニュース', 2 => '2ch', 3 => 'ブログ');

	echo $this->Form->create(array('action' => 'register'));
	//echo $this->Form->input('Site.url', array('type' => 'text', 'default' => ' '));
	?>
	<div class="input text"><label for="SiteUrl">Url</label><input name="data[Site][url]" value="" id="SiteUrl" type="text"></div>
	<?php
	echo $this->Form->input('Site.category_id', array('type' => 'select', 'options' => $categories, 'selected' => 1));
	echo $this->Form->submit('登録');
	echo $this->Form->end();
?>
	</fieldset>
</div>