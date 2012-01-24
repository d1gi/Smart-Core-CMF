
<!--<h1>Управление каталогом</h1>-->

<div class="default-form">

<fieldset><legend>Cвойства в группе</legend>
	<table class="admin-table">
	<tr>
		<th width="1%">id</th>
		<th width="1%">pos</th>
		<th width="1%">Кол-во записей</th>
		<th>Название</th>
		<th>Техническое имя</th>
		<th>Тип</th>
		<th>Параметры</th>
		<th width="1%">Обязателен для заполнения</th>
		<th width="1%">Отображать в списке админа</th>
		<th width="1%">Отображать в списке записей</th>
		<th width="1%">Отображать при просмотре записи</th>
		<th width="1%">Пустое значение как NULL</th>
	</tr>
	<?php
	foreach ($this->properties_list as $key => $value) {
		echo "<tr>\n";
		echo "\t<td><a href=\"?edit_property=$key\">$key</a></td>\n";
		echo "\t<td>$value[pos]</td>\n";
		echo "\t<td>$value[items_count]</td>\n";
		
		echo "\t<td><a href=\"?edit_property=$key\">\n";
		if ($value['is_active'] == 0) {
			echo "<span style=\"text-decoration: line-through;\">$value[title]</span>";
		} else {
			echo "$value[title]";
		}
		echo "</a></td>\n";
		
		
		$params = str_replace(" ", "&nbsp;", $value['params_yaml']);
		$params = str_replace("\n", "\n<br />", $params);
		echo "\t<td>$value[name]</td>\n";
		echo "\t<td>$value[type]</td>\n";
		echo "\t<td><div style=\"overflow: hidden;width: 100%;\">$params</div></td>\n";
		echo "\t<td>$value[is_required]</td>\n";
		echo "\t<td>$value[show_in_admin]</td>\n";
		echo "\t<td>$value[show_in_list]</td>\n";
		echo "\t<td>$value[show_in_view]</td>\n";
		echo "\t<td>$value[empty_as_null]</td>\n";
		echo "</tr>\n";
	}	
	?>
</table>
</fieldset>

<fieldset><legend>Создать свойство</legend>
	<?php $Form = new Helper_Form($this->create_property_form_data); echo $Form;?>
</fieldset>

<fieldset><legend>Группа свойств</legend>
	<?php $Form = new Helper_Form($this->edit_properties_group_form_data); echo $Form;?>
</fieldset>


</div>

<a href="?properties">&laquo; Назад</a>
