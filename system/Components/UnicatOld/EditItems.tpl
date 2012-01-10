<h3>Записи</h3>

<div class="paginator">
<?php echo $data['pages'];?>
</div>

<?php
	$query_string = '?';
	foreach ($_GET as $key => $value) {
		if (strpos($key, 'order_by') === false) {
			$query_string .= $key;
			if (!empty($value)) {
				$query_string .= '=' . $value;
			}
			$query_string .= '&';
		}
	}
?>

<table class="admin-table">
	<tr>
		<th>id</th>
		<!--<th><a href="<?php echo $query_string;?>order_by_title=asc">Заголовок</a></th>-->
		<th>Заголовок</th>
		<th>URI</th>
		<th>Дата создания</th>
		<th>Владелец</th>
		<th>Включена?</th>
	</tr>
<?php
	foreach ($data['items'] as $item_id => $value) {
		echo "<tr>\n";
		echo "\t<td><a href=\"?edit_item=$item_id&admin\">$item_id</a></td>\n";
		echo "\t<td><a href=\"?edit_item=$item_id&admin\">{$value['properties']['content']['title']['value']}</a></td>\n";
		echo "\t<td>{$value['properties']['uri_part']}</td>\n";
		echo "\t<td>{$value['properties']['create_datetime']}</td>\n";
		echo "\t<td>{$value['properties']['owner_id']}</td>\n";
		echo "\t<td>{$value['properties']['is_active']}</td>\n";
		echo "</tr>\n";
	}
	
?>
</table>

<div class="paginator">
<?php echo $data['pages'];?>
</div>

<br />
Отображается по <?php echo $data['pages']->getItemsPerPage();?> записей на страницу.
<br /><br />
<a href="?">&laquo; Назад</a>
