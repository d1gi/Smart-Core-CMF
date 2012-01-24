
<fieldset><legend>Коллекции</legend>
<table class="admin-table" width="100%">
	<tr>
		<th width="1px">id</th>
		<th>Сomponent</th>
		<th>Title</th>
		<th>Default Storage</th>
		<th>Relative path<div style="font-weight: normal;">(Относительный путь в хранилище)</div></th>
		<th>Description</th>
		<th>Params</th>
	</tr>
	<?php
	foreach ($this->collections as $key => $value) {
		echo "<tr>\n";
		echo "\t<td>$key</td>\n";
		echo "\t<td><b><a href=\"" . HTTP_ROOT . ADMIN . "/component/Media/collection/$key/\">$value[name]</a></b></td>\n";
		echo "\t<td>$value[title]</td>\n";
		echo "\t<td>$value[default_storage_id]</td>\n";
		echo "\t<td>$value[relative_path]</td>\n";
		echo "\t<td>$value[descr]</td>\n";
		echo "\t<td><ul>";
		
		foreach ($value['params'] as $key => $value) {
			echo "<li>$key = $value</li>";
		}
		
		echo "</ul></td>\n";
		echo "</tr>\n";
	}
	?>
</table>

<h3>Создать новую коллекцию</h3>
<?php 
$Form = new Helper_Form($this->create_collection_form_data);
echo $Form;
?>
</fieldset>


<fieldset><legend>Хранилища</legend>
<table class="admin-table" width="100%">
	<tr>
		<th width="1px">id</th>
		<th>Storage name</th>
		<th>Title</th>
		<th>Path</th>
		<th>Description</th>
	</tr>
	<?php

	foreach ($this->storages as $key => $value) {
		echo "<tr>\n";
		echo "\t<td>$key</td>\n";
		echo "\t<td><b><a href=\"storage/$key/\">$value[name]</a></b></td>\n";
		echo "\t<td>$value[title]</td>\n";
		
		if (strlen($value['path']) == 0) {
			echo "\t<td>" . HTTP_ROOT . "</td>\n";
		} else {
			echo "\t<td>$value[path]</td>\n";
		}
		
		echo "\t<td>$value[descr]</td>\n";
		echo "</tr>\n";
	}
	?>
</table>

<h3>Создать новое хранилище</h3>
<?php 
$Form = new Helper_Form($this->create_storage_form_data);
echo $Form;
?>

</fieldset> 

