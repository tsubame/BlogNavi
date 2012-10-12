記事のリスト
<?php

	foreach ($results as $data) {
		echo $data['Article']['title'] . '<br />';
		echo $data['Article']['tweeted_count'] . '<br />';
		echo $data['Article']['url'] . '<br /><br />';
	}

?>