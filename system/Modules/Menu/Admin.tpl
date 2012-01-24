
<fieldset><legend>Список всех групп</legend>
<table class="admin-table" width="100%">
	<tr>
		<th>ID</th>
		<th>Name</th>
		<th>Description</th>
		<th>Pos</th>
		<th>Items</th>
		<th>Action</th>
	</tr>        
<?php
	foreach ($this->groups_list as $group_id => $value) {
		echo "<tr>\n";
		echo "<td>$group_id</td>";
		echo "<td>$value[name]</td>";
		echo "<td>$value[descr]</td>";
		echo "<td>$value[pos]</td>";
		echo "<td>$value[items_count]</td>";
		
		if ($value['items_count'] == 0) {
			echo "<td><a href=\"?del_group=$group_id\" onclick=\"return confirm('Вы уверены, что хотите удалить группу: $value[descr]?')\">Удалить</a></td>\n";
		} else {
			echo "<td>Удалить</td>\n";
		}
		
		echo "</tr>\n";
	}
	?>
</table>
</fieldset>

<fieldset><legend>Создать новую группу</legend>
<?php
$Form = new Helper_Form($this->create_group_form_data);
echo $Form;
?>
</fieldset>
