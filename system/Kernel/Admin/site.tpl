<?php
/**
 * @version 2011-07-18.0
 */

// Отображение меню.
if (isset($data['menu'])) {
	foreach ($data['menu'] as $key => $value) {
		if (isset($value['selected']) and $value['selected'] == true) {
			echo "<b><a href=\"$value[link]\">$value[title]</a></b> | ";
		} else {
			echo "<a href=\"$value[link]\">$value[title]</a> | ";
		}
	}
	echo "<br /><br />\n\n";
}

// Упраление мета-данными.
if (isset($data['meta_controls'])) {
	$Meta = new Component_Meta();
	$Meta->renderControls($data['meta_controls']);
}

// Отображения формы редактирования сайта.
if (isset($data['site_edit_form'])) {
	$Form = new Helper_Form($data['site_edit_form']);
	echo $Form;
}

// Отображение формы редактирования доменов.
if (isset($data['domains'])) { ?>
	<form action="" method="post" class="default-form">
	<table class="admin-table" width="100%">
	<tr>
		<th>Домен</th>
		<th>Описание</th>
		<th>Дата подключения</th>
		<th>Действие</th>
	</tr>
	<?php
	foreach ($data['domains'] as $key => $value) {
		
		if ($_SERVER['HTTP_HOST'] === $key) {
			$disabled = ' disabled="disabled"';
		} else {
			$disabled = '';
		}
		
		echo "<tr>\n";
		echo "\t<td><input name=\"pd[$key][domain]\" value=\"$key\" type=\"text\" $disabled/></td>\n";
		echo "\t<td><input name=\"pd[$key][descr]\" value=\"$value[descr]\" type=\"text\"/></td>\n";
		echo "\t<td>$value[create_datetime]</td>\n";
		echo "\t<td><input type=\"hidden\" name=\"pd[$key][delete]\" value=\"0\"/><input name=\"pd[$key][delete]\" value=\"1\" type=\"checkbox\"$disabled/> Удалить</td>\n";
		echo "</tr>\n";
	}
	echo "<tr>\n";
	echo "\t<td><input name=\"pd[_new_domain_][domain]\" value=\"\" type=\"text\"/></td>\n";
	echo "\t<td><input name=\"pd[_new_domain_][descr]\" value=\"\" type=\"text\"/></td>\n";
//			echo "\t<td><input name=\"pd[_new_domain_][ga_id]\" value=\"\" type=\"text\"/></td>\n";
	echo "\t<td> <- Добавить домен</td>\n";
	echo "</tr>\n";
	?>
	</table>
	<div class="field">
		<input name="submit[update_domains]" value="Сохранить домены" type="submit"/>
	</div>
	</form>
	<?php
}
