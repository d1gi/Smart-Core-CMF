<h3>Записи</h3>

<div class="paginator">
<?php echo $this->pages;?>
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
		<th>URI</th>
<?php
	foreach ($this->items as $item_id => $value) {
		foreach ($value['properties']['content'] as $property) {
			echo "<th>{$property['title']}</th>\n\t";
		}
		break;
	}
?>		
		<th>Включена?</th>
		<th>Владелец</th>
		<th>Дата создания</th>
		
	</tr>
<?php
	foreach ($this->items as $item_id => $value) {
		echo "<tr>\n";
		echo "\t<td><a href=\"?edit_item=$item_id&admin\">$item_id</a></td>\n";
		echo "\t<td><b>{$value['properties']['uri_part']}</b></td>\n";
		foreach ($value['properties']['content'] as $property) {
			if (strlen($property['value']) == 0) {
				echo "\t<td>&nbsp;</td>\n";
			} else {
				echo "\t<td><a href=\"?edit_item=$item_id&admin\">{$property['value']}</a></td>\n";
			}
		}
		echo "\t<td>{$value['properties']['is_active']}</td>\n";
		echo "\t<td>{$value['properties']['owner_id']}</td>\n";
		echo "\t<td>{$value['properties']['create_datetime']}</td>\n";
		echo "</tr>\n";
	}
	
?>
</table>

<div class="paginator">
<?php echo $this->pages;?>
</div>

<br />
Отображается по <?php echo $this->pages->getItemsPerPage();?> записей на страницу.
<br /><br />
<a href="?">&laquo; Назад</a>
