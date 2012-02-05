<?php
/**
 * @version 2012-02-01
 */

$level = 0;

// Форма редактирования папки.
$Form = new Helper_Form($this->form);
echo $Form;

// Упраление мета-данными.
if ($this->meta_controls) {
	$Meta = new Component_Meta();
	$Meta->renderControls($this->meta_controls);
}

// Отображение списка всех папок.
if ($this->all) {
	foreach ($this->all as $key => $value) {
		if (is_numeric($key)) {
			
			$bc = Registry::get('Breadcrumbs');
			
			echo "<div style=\"float: right;\"><a href=\"" . $bc->getLastUri() . "create/1/\">Добавить папку</a></div>";
			echo "<h2>Вся структура папок</h2><ul>\n";
			
			foreach ($this->all as $key => $value) {
				$edit_href = "<a href=\"" . $bc->getLastUri() . $key . "/\" title=\"http://" . $_SERVER['HTTP_HOST'] . $value['link'] . "\">" . $value['title'] . " (pos: " . $value['pos'] . ")</a>";
				
				if ($value['is_active'] == 0) {
					$edit_href = "<span style=\"text-decoration: line-through;\">$edit_href</span>";
				}
				
				$tab = '';
				for ($i = 0; $i <= $level; $i++) {
					$tab .= "\t";
				}
				echo $tab;
				
				if ($level == $value['level']) {
					echo "<li>$edit_href\n";
				} elseif ($level < $value['level']) {
					echo "<ul>\n<li>$edit_href\n";
					$level = $value['level'];
				} else {
					echo "</ul>\n<li>$edit_href</li>\n";
					$level--;
				}
			}
			echo "</ul>\n\n";
			echo "</ul>\n\n";
			echo "<hr /><a href=\"" . $bc->getLastUri() . "create/1/\">Добавить папку</a>";
			
			break;
		} else {
			break;
		}
	}
}

if (!empty($this->nodes)) {
	echo "Все ноды в этой папке:<ul>";
	foreach ($this->nodes as $key => $value) {
		$descr = "$value[descr] ($value[module_id])";
		if ($value['is_active'] == 0) {
			$descr = "<span style=\"text-decoration: line-through;\">$descr</span>";
		}

		echo "<li>id: $key - <a href=\"" . HTTP_ROOT . ADMIN . "/structure/node/" . $key . "/\">$descr</a></li>";
	}
	echo "</ul>";

}

echo "Мета-данные: <a href=\"meta/\">Редактировать</a><br />";
if (is_array($this->meta)) {
	echo "<table class=\"admin-table\">";
	foreach ($this->meta as $key => $value) {
		echo "<tr><td>$key</td><td>$value</td></tr>";
	}
	echo "</table>";
}
