<?php 

	$Form = new Helper_Form($data['edit_form']);
	echo $Form;

?>	
<fieldset><legend>Группы</legend>
	<table class="admin-table">
	<tr>
		<th>id</th>
		<th>name</th>
		<th>descr</th>
		<th>includes</th>
	</tr>
	<?php
	foreach ($data['groups_list'] as $key => $value) {
		$includes = '';
		foreach ($value['includes'] as $lvl => $data) {
			foreach ($data as $group_id => $group_name) {
				$includes .= "[$group_id] $group_name <br />\n";
			}
		}
		
		echo "<tr>\n";
		echo "\t<td>$key</td>\n";
		echo "\t<td>$value[name]</td>\n";
		echo "\t<td><a href=\"#" . HTTP_ROOT . ADMIN . "/users/group/$key/\">$value[descr]</a></td>\n";
		echo "\t<td>$includes</td>\n";
		echo "</tr>\n";
	}
	?>
	</table>
</fieldset>
	
