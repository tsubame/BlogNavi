
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $title_for_layout; ?>
	</title>
	<?php
		echo $this->Html->meta('icon');

		echo $this->Html->css('common');

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body>
	<div id="container">
		<div id="header">
			<?php
				echo $this->Html->link('記事一覧', array('controller' => 'articles','action' => 'index')) . '　　';
				echo $this->Html->link('記事の登録', array('controller' => 'articles', 'action' => 'insert')) . '　　';
				echo $this->Html->link('ツイート数の更新',array('controller' => 'articles', 'action' => 'getTweetCount')) . '　　';

				echo $this->Html->link('サイトの一覧', array('controller' => 'sites', 'action' => 'index')) . '　　';
				echo $this->Html->link('サイトの登録', array('controller' => 'sites', 'action' => 'registerForm')) . '　　';
			?>
		</div>
		<div id="content">
			<?php echo $this->Session->flash(); ?>
			<?php echo $this->fetch('content'); ?>
		</div>

	</div>
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>

