<?php
/**
 * @version 2011-07-18.1
 */

// Редактирование текстера
if (isset($this->edit_form_data)) {
	$Form = new Helper_Form($this->edit_form_data);
	echo $Form;
	//&nbsp;|&nbsp;<a href="javascript:toggletinyMCE('id-pd-text-0');">Вкл/Выкл визуальный редактор</a>
	?><div style="width: 100%; text-align: right; margin-top: -40px;"><a href="meta/">Мета-данные</a></div><?php
}

if (isset($this->all_items)) {
	?>
	<table class="admin-table" width="100%">
		<tr>
			<th>ID</th>
			<th>Длина</th>
			<th>Нода</th>
			<th>Описание</th>
			<th>Путь</th>
			<th>Блок id</th>
			<th>db id</th>
			<th>Действие</th>
		</tr>        
		<?php
		foreach ($this->all_items as $item_id => $value) {
			$rowspan = count($value['nodes']);
			
			echo "<tr>\n";

			if ($rowspan > 1) {
				echo "\t<td rowspan=\"$rowspan\"><a href=\"$item_id/\">$item_id</a></td>\n";
				echo "\t<td rowspan=\"$rowspan\">$value[content_length]</td>\n";
			} else {
				echo "\t<td><a href=\"$item_id/\">$item_id</a></td>\n";
				echo "\t<td>$value[content_length]</td>\n";
			}

			$cnt = 0;
			foreach ($value['nodes'] as $node_id => $prop) {
				if ($rowspan > 1 and $cnt > 0) {
					echo "<tr>\n";	
				}
				
				$path = Folder::getUri($prop['folder_id']);
				
				echo "\t<td><a href=\"" . HTTP_ROOT . ADMIN . "/structure/node/$node_id/\" title=\"Свойства ноды\">$node_id</a></td>\n";
				if ($prop['is_active']) {
					echo "\t<td><a href=\"" . $path . ACTION . "/$node_id/edit/\" title=\"Редактирвать средствами ноды\">$prop[descr]</a></td>\n";
				} else {
					echo "\t<td><span style=\"text-decoration: line-through;\">$prop[descr]</span></td>\n";
				}
				
				echo "\t<td>$path</td>\n";
				echo "\t<td>$prop[block_id]</td>\n";
				echo "\t<td>$prop[database_id]</td>\n";

				if ($rowspan > 1 or $cnt > 0) {
					echo "</tr>\n";
				}
				$cnt++;
			}
			
			if (count($value['nodes']) == 0) {
				echo "<td></td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td></td>";
				echo "<td><a href=\"?del_item=$item_id\" onclick=\"return confirm('Вы уверены, что хотите удалить текстовую запись: $item_id?')\">Удалить</a></td>\n";
			}
			
			echo "</tr>\n";
		}
		?>
	</table>
	
	<div class="paginator">
	<?php echo $this->pages?>
	</div>
	
	<?php
}
