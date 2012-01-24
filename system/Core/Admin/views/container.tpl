
<form action="" method="post" class="default-form">
<input type="hidden" name="action" value="update_containers"/>
	<table class="admin-table" width="100%">
	<tr>
		<th>id</th>
		<th>pos</th>
		<th>Имя <span style="color: red; cursor: help;" title="Обязателен для заполнения">*</span></th>
		<th>Описание</th>
		<th>Наследование в папках<div style="font-weight: normal;">(перечислить через запятую id папок)</div></th>
		<th>Кол-во нод</th>
		<th>Действие</th>
	</tr>
	<?php
	foreach ($this->containers as $key => $value) {
		
		$disabled = $value['nodes_count'] > 0 ? ' disabled="disabled"' : '';
		
		echo "<tr>\n";
		echo "\t<td>$key</td>\n";
		echo "\t<td width=\"40\"><input name=\"pd[$key][pos]\" value=\"$value[pos]\" type=\"text\"/></td>\n";
		echo "\t<td><input name=\"pd[$key][name]\" value=\"$value[name]\" type=\"text\"/></td>\n";
		echo "\t<td><input name=\"pd[$key][descr]\" value=\"$value[descr]\" type=\"text\"/></td>\n";
		echo "\t<td><input name=\"pd[$key][inherit]\" value=\"$value[inherit]\" type=\"text\"/></td>\n";
		echo "\t<td>$value[nodes_count]</td>\n";
		echo "\t<td nowrap><input type=\"hidden\" name=\"pd[$key][delete]\" value=\"0\"/><input name=\"pd[$key][delete]\" value=\"1\" type=\"checkbox\"$disabled/> Удалить</td>\n";
		echo "</tr>\n";
	}
	echo "<tr>\n";
	echo "\t<td></td>\n";
	echo "\t<td><input name=\"pd[_new_container_][pos]\" value=\"\" type=\"text\"/></td>\n";
	echo "\t<td><input name=\"pd[_new_container_][name]\" value=\"\" type=\"text\"/></td>\n";
	echo "\t<td><input name=\"pd[_new_container_][descr]\" value=\"\" type=\"text\"/></td>\n";
	echo "\t<td colspan=\"3\"> <- Добавить контейнер</td>\n";
	echo "</tr>\n";
	?>
</table>

<input name="submit[update_containers]" value="Сохранить контейнеры" type="submit"/>

</form>
