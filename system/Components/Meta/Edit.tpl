<?php
/**
 * Шаблон отображения интерфейса управления мета тэгами.
 * @version 2011-07-18.0
 */

// Список мета тэгов
if (isset($data['meta_list']) and is_array($data['meta_list']) and count($data['meta_list']) > 0) {
	?>
	<form action="" method="post" class="default-form">
	<?php
	if (isset($data['meta_create_form']['hiddens'])) {
		foreach ($data['meta_create_form']['hiddens'] as $key => $value) {
			echo "<input type=\"hidden\" name=\"$key\" value=\"$value\"/>";
		}
	}
	?>
	<table class="admin-table" width="100%">
	<tr>
		<th width="180px">Имя тэга</th>
		<th>Содержание</th>
		<th>Действие</th>
	</tr>
	<?php
	foreach ($data['meta_list'] as $key => $value) {
		echo "<tr>\n";
		echo "\t<td>$key</td>\n";
		echo "\t<td><textarea name=\"pd[$key]\" style=\"width: 100%; height: 50px;\"/>$value</textarea></td>\n";
		echo "\t<td><input name=\"pd[$key][delete]\" value=\"0\" type=\"checkbox\"/>Удалить</td>\n";
		echo "</tr>\n";
	}
	?>
	</table>
	<div class="field">
		<input name="submit[update_meta_tags]" value="Сохранить мета-тэги" type="submit"/>
	</div>
	</form>
	<?php
}

// Добавление нового мета-тэга.
if (isset($data['meta_create_form'])) {
	$Form = new Helper_Form($data['meta_create_form']);
	echo $Form;
}
