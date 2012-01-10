<h3>Категории</h3>

	<fieldset><legend>Дерево категорий</legend>
	<?php

	$level = 0;
	$first_child = true;

	echo "\n<ul>\n";
	foreach ($data['categories_list'] as $key => $value) {
		
		$tab = '';
		for ($i = 0; $i <= $value['level'] - 1; $i++) {
			$tab .= "\t";
		}
		
		$anchor = "<a href=\"?edit_category=" . $key . "\" title=\"http://" . $_SERVER['HTTP_HOST'] . $value['uri'] ."\" >" . $value['title'] . " (pos: " . $value['pos'] . ") </a>";
		
		if ($value['is_active'] == 0) {
			$anchor = "<span style=\"text-decoration: line-through;\">$anchor</span>";
		}

	//	$anchor = "<a href=\"" . $value['uri'] . "\" title=\"" . $value['descr'] . "\">" . $value['title'] . "</a>";
	//	$anchor = "<a href=\"" . $value['uri'] . "\" >" . $value['title'] . "</a>";
//		$anchor = "<a href=\"?edit_category=" . $key . "\" >" . $value['title'] . " (pos: " . $value['pos'] . ") </a>";

		/*	
		// Уровень совпадает. 
		if ($level == $value['level']) {
			echo "<li>";
		} elseif ($level < $value['level']) {
			echo "<ul>\n";
			echo "<li>";
		} else {
			echo "</li>\n</ul>\n";
		}
		
		*/

		if ($level == $value['level']) {
			if ($first_child) {
				$first_child = false;
				echo "$tab<li>$anchor";
			} else {
				echo "</li>\n$tab<li>$anchor";
			}
		} elseif ($level < $value['level']) { // 
			$level = $value['level'];
			echo "\n$tab<ul>\n$tab<li>$anchor";
		} else {
			$cnt = $level - $value['level'];
			while ($cnt--) {
				echo "$tab\t</li>\n$tab\t</ul>\n";
			}
			echo "$tab</li>\n$tab<li>$anchor";
			$level = $value['level'];
		}
		
	}

	echo "\n</li>\n</ul>\n\n";	
	?>
	</fieldset>

	<fieldset><legend>Добавить новую категорию</legend>
	<?php $Form = new Helper_Form($data['new_category_form_data']); echo $Form;?>
	</fieldset>

<a href="?">&laquo; Назад</a>
	