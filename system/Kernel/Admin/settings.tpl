
<form action="" method="post" class="default-form">
<input type="hidden" name="action" value="update_settins"/>
	<table class="admin-table">
	<tr>
		<th width="10%">variable</th>
		<th>Текущее значение</th>
		<th width="10%">Значение по умолчанию</th>
		<th width="10%">Описание</th>
	</tr>
	<?php
	foreach ($data as $key => $value) {
		echo "<tr>\n";
//		echo "\t<td><a href=\"" . HTTP_ROOT . ADMIN . "/settings/edit/$key/\">$key</a></td>\n";
		echo "\t<td>$key</td>\n";
		echo "\t<td><input name=\"pd[$key]\" value=\"$value[value]\" type=\"text\"/></td>\n";
		echo "\t<td>$value[default_value]</td>\n";
		echo "\t<td>$value[descr]</td>\n";
		echo "</tr>\n";
	}
	?>
</table>

<input name="submit[update_settins]" value="Сохранить настройки" type="submit"/>

</form>
