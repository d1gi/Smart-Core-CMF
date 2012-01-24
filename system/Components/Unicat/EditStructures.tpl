<h3>Структуры категорий</h3>

<div class="default-form">

<!--<ul> <fieldset><legend>Группы свойств</legend>-->

<?php

if (!empty($this->structures_list)) { ?>
	<form action="" method="post" class="default-form">
	<input type="hidden" name="node_id" value="<?php echo $this->node_id?>"/>
	<table class="admin-table" width="100%">
	<tr>
		<th>id</th>
		<th width="1%">pos</th>
		<th title="Используется как часть УРИ">name</th>
		<th title="Описание для админа">descr</th>
		<th title="Имя таблицы">table</th>
		<th title="Обязателен для заполенения">req.</th>
		<th title="Вхождения: single - одиночное; multi - множественное; [int] - указать числом максимально возможное.">entries</th>
		<th title="Кол-во записей">items count</th>
		<th title="Действие">action</th>
	</tr>

	<?php
	foreach ($this->structures_list as $key => $value) {
		
		if ($value['reqired'] == 1) {
			$required = ' checked="checked"';
		} else {
			$required = '';
		}
		
		$disabled = '';
//		$disabled = ' disabled="disabled"';
		
		echo "<tr>\n";
		echo "\t<td>$value[id]</td>\n";
		echo "\t<td><input name=\"pd[$key][pos]\" value=\"$value[pos]\" type=\"text\"/></td>\n";
		echo "\t<td><input name=\"pd[$key][name]\" value=\"$value[name]\" type=\"text\"/></td>\n";
		echo "\t<td><input name=\"pd[$key][descr]\" value=\"$value[descr]\" type=\"text\"/></td>\n";
		echo "\t<td><input name=\"pd[$key][table]\" value=\"$value[table]\" type=\"text\"/></td>\n";
		echo "\t<td><input type=\"hidden\" name=\"pd[$key][reqired]\" value=\"0\"/><input name=\"pd[$key][reqired]\" value=\"1\"$required type=\"checkbox\"/></td>\n";
//		echo "\t<td><input name=\"pd[$key][reqired]\" value=\"$value[reqired]\" type=\"text\"/></td>\n";
		echo "\t<td><input name=\"pd[$key][entries]\" value=\"$value[entries]\" type=\"text\"/></td>\n";
		echo "\t<td>@todo</td>\n";
		echo "\t<td><input type=\"hidden\" name=\"pd[$key][delete]\" value=\"0\"/><input name=\"pd[$key][delete]\" value=\"1\" type=\"checkbox\"$disabled/> Удалить</td>\n";
		echo "</tr>\n";
	}
	?>

	</table>
	<div class="field">
		<input name="submit[update_structures]" value="Сохранить структуры" type="submit"/>
	</div>
	</form>
	<?php
}


//	foreach ($this->properties_groups_list as $key => $value) {
//		echo "\t<li><a href=\"?edit_properties_group=$key\">$value[title]</a></li>\n";				
//	}
?>

<!--</ul> </fieldset>-->

<fieldset><legend>Добавить структуру категорий</legend>
<?php $Form = new Helper_Form($this->new_structure_form_data); echo $Form;?>
</fieldset>

</div>

<a href="?">&laquo; Назад</a>

