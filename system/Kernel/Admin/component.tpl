
<table class="admin-table" width="100%">
	<tr>
		<th width="1px">№</th>
		<th>Сomponent</th>
		<th>Title</th>
		<th>Description</th>
		<!--<th>Действие</th>-->
	</tr>
	<?php
	$cnt = 1;
	foreach ($data['components'] as $key => $value) {
		echo "<tr>\n";
		echo "\t<td>" . $cnt++ . "</td>\n";
		
		if ($value['is_managed']) {
			echo "\t<td><b><a href=\"$key/\">$key</a></b></td>\n";
		} else {
			echo "\t<td>$key</td>\n";
		}
		
		echo "\t<td>$value[title]</td>\n";
		echo "\t<td>$value[descr]</td>\n";
		echo "</tr>\n";
	}
	?>
</table>
