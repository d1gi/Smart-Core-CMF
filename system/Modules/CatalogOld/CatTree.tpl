
<b>Категории каталога:</b>

<?php

$level = 0;
$first_child = true;

if (strlen(@$data['css_class']) > 0) {
	echo "\n<ul class=\"{$data['css_class']}\">\n";
} else {
	echo "\n<ul>\n";
}

foreach ($data['categories_list'] as $key => $value) {
	
	$tab = '';
	for ($i = 0; $i <= $value['level'] - 1; $i++) {
		$tab .= "\t";
	}
	
	$selected = '';
	if ($value['selected']) {
		$selected = ' class="selected"';
	}

//	$anchor = "<a href=\"" . $value['uri'] . "\" title=\"" . $value['descr'] . "\">" . $value['title'] . "</a>";
	$anchor = "<a href=\"" . $value['uri'] . "\"$selected>" . $value['title'] . "</a>";

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
