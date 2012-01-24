<?php
/**
 * @version 2012-01-24.0
 */

// Упраление мета-данными.
if ($this->meta_controls) {
	$Meta = new Component_Meta();
	$Meta->renderControls($this->meta_controls);
}

// Отображения формы редактирования сайта.
if ($this->site_edit_form) {
	$Form = new Helper_Form($this->site_edit_form);
	echo $Form;
}

// Отображение формы редактирования доменов.
if ($this->domains) { ?>
	<form action="" method="post" class="default-form">
	<table class="admin-table" width="100%">
	<tr>
		<th>Домен</th>
		<th>Описание</th>
		<th>Дата подключения</th>
		<th>Действие</th>
	</tr>
	<?php
	foreach ($this->domains as $key => $value) {
		
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
