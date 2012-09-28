<!-- フォームヘルパーを使うべき -->


<div>
<!--
サイトの登録<br /><br />
	<form method="POST" action="insert" class="registerForm">
		<table>
			<tr>
				<td class="formHead">URL：</td>
				<td><input type="text" name="url" /></td>
			</tr>
			<tr>
				<td>カテゴリー：</td>
				<td>
					<select name="category">
						<option>ニュースサイト</option>
						<option>ブログ</option>
					</select>
					<input type="checkbox" name="categoryFix" value="true" />カテゴリ固定
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><br /><input type="submit" value="登録"/></td>
			</tr>
		</table>
	</form>
</div>
-->


<?php
echo $this->Form->create(array('action' => './register'));
//echo $this->Form->input('Site.name', array('type' => 'hidden'));
echo $this->Form->input('Site.url');
echo $this->Form->submit('登録');
echo $this->Form->end();
?>