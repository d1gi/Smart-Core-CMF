Управление слайдами.

<br /><br />
<table class="admin-table" width="100%">
<form action="" method="post">
	<input type="hidden" name="node_id" value="<?php echo $data['node_id']; ?>">
	<tr>
		<th width="1%">Слайд</th>
		<th>Позиция</th>
		<th>Действие</th>
	</tr>

<?php
foreach ($data['slides'] as $key => $value) {
	echo "<tr>
		<td><img src=\"$value[img]\" width=\"300\"></td>
		<td><input type=\"text\" name=\"pd[$key]\" value=\"$value[pos]\" /></td>
		<td><a href=\"?delete_img=$key\">Удалить</a></td>
	</tr>";
}
?> 
</table>

<input type="submit" name="submit[recalculate]" value="Пересчитать позиции" />
</form>

<?php 
$Form = new Helper_Form($data['edit_form_data']);
echo $Form;
