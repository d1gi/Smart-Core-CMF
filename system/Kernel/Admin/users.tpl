
<?php
if (isset($data['edit_form'])) {
	include 'users_edit.tpl';
}

if (isset($data['manage'])) {
?>
<fieldset><legend>Список пользователей</legend>
	<table class="admin-table">
	<tr>
		<th>id</th>
		<th>вкл?</th>
		<!--<th>Логин</th>-->
		<th>Псевдоним</th>
		<th>Полное имя</th>
		<th>email</th>
		<th>Пол</th>
		<th>Дата рождения</th>
		<th>timezone</th>
		<th>Дата регистрации</th>
		<th>groups</th>
	</tr>
	<?php
	foreach ($data['manage']['users'] as $key => $value) {
		$groups = '';
		foreach ($value['groups'] as $group_id => $group_name) {
			$groups .= "[$group_id] $group_name <br />\n";
		}
		echo "<tr>\n";
		echo "\t<td>$key</td>\n";
		echo "\t<td>$value[is_active]</td>\n";
//		echo "\t<td>$value[login]</td>\n";
		echo "\t<td><a href=\"" . HTTP_ROOT . ADMIN . "/users/edit/$key/\">$value[nickname]</a></td>\n";
		echo "\t<td>$value[fullname]</td>\n";
		echo "\t<td>$value[email]</td>\n";
		echo "\t<td>$value[gender]</td>\n";
		echo "\t<td>$value[dob]</td>\n";
		echo "\t<td>$value[timezone]</td>\n";
		echo "\t<td>$value[create_datetime]</td>\n";
		echo "\t<td>$groups</td>\n";
		echo "</tr>\n";
	}
	?>
</table>
</fieldset>


<fieldset><legend>Группы</legend>
	<table class="admin-table">
	<tr>
		<th>id</th>
		<th>name</th>
		<th>descr</th>
		<th>includes</th>
	</tr>
	<?php
	foreach ($data['manage']['groups'] as $key => $value) {
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


<?php
} // end manage...
