<?php
/**
 * 削除可能な記事のリスト
 */
?>

<p>記事のリスト</p>
<p>
<?php 	echo $this->Html->link('すべて', array(
			'controller' => 'articles',
			'action' => 'index')
		). '　　';
		echo $this->Html->link('ニュース',
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
		echo '　　 ' . $data['Site']['name'];
		?>
		</p>
		<?php
	}

?>