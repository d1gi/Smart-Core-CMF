
<!--<h1>Управление каталогом</h1>-->

<div class="default-form">

<fieldset><legend>Группа свойств</legend>
	<?php $Form = new Helper_Form($data['edit_properties_group_form_data']); echo $Form;?>
</fieldset>


<fieldset><legend>Cвойства в группе</legend>
	<table class="admin-table">
	<tr>
		<th>id</th>
		<th>pos</th>
		<th>Название</th>
		<th>Техническое имя</th>
		<th>Тип</th>
		<th>Параметры</th>
		<th>Обязателен для заполнения</th>
		<th>Отображать в списке админа</th>
		<th>Отображать в списке записей</th>
		<th>Отображать при просмотре записи</th>
		<th>Кол-во записей</th>
	</tr>
	<?php
	foreach ($data['properties_list'] as $key => $value) {
		echo "<tr>\n";
		echo "\t<td>$key</td>\n";
		echo "\t<td>$value[pos]</td>\n";
		
		echo "\t<td><a href=\"?edit_property=$key\">\n";
		if ($value['is_active'] == 0) {
			echo "<span style=\"text-decoration: line-through;\">$value[title]</span>";
		} else {
			echo "$value[title]";
		}
		echo "</a></td>\n";
		
		echo "\t<td>$value[name]</td>\n";
		echo "\t<td>$value[type]</td>\n";
		echo "\t<td><div style=\"overflow: hidden;width: 100%;\">" . str_replace(';s', ';<br />s', $value['params']) . "</div></td>\n";
		echo "\t<td>$value[is_required]</td>\n";
		echo "\t<td>$value[show_in_admin]</td>\n";
		echo "\t<td>$value[show_in_list]</td>\n";
		echo "\t<td>$value[show_in_view]</td>\n";
		echo "\t<td>$value[items_count]</td>\n";
		echo "</tr>\n";
	}	
	?>
</table>
</fieldset>

<fieldset><legend>Создать свойство</legend>
	<?php $Form = new Helper_Form($data['create_property_form_data']); echo $Form;?>
</fieldset>


</div>

<a href="?properties">&laquo; Назад</a>
