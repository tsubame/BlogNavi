<p>記事のリスト</p>
<p>
	<a href = "/articles/index">すべて</a>　
<?php echo $this->Html->link( 'ニュース',
		array(
			'controller' => 'articles',
			'action' => 'index',
			'1')
		) . '　　';

	echo $this->Html->link( '2ch', array('2')) . '　　';
	echo $this->Html->link( 'ブログ', array('3')) . '　　';
?>
</p>
<br />
<br />
<?php

	foreach ($results as $i => $data) {
		if ($i % 10 == 0) {
			echo '<br /><br />';
		}

		?>
		<p>
		<?php
		echo $data['Article']['tweeted_count'] . 'tweet　　';
		echo $this->Html->link($data['Article']['title'], $data['Article']['url'], array('target' => '_blank'));
		?>
		</p>
		<?php
	}

?>