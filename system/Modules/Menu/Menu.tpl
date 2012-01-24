<?php 
/*
foreach ($this as $key => $value) {
	cmf_dump($key);
}

cmf_dump(self::getPaths());

if (in_array('\var\www\site.ru\system\\', self::getPaths())) {
	cmf_dump('<b>yes</b>');
}
*/
$level = 0;
$first_child = true;

echo strlen($this->css_class) > 0 ? "\n<ul class=\"{$this->css_class}\">\n" : "\n<ul>\n";

foreach ($this->items as $key => $value) {
	$tab = '';
	for ($i = 0; $i <= $value['level'] - 1; $i++) {
		$tab .= "\t";
	}
	
	$selected = '';
	if ($value['selected']) {
		$selected = ' class="selected"';
	}

	$anchor = "<a href=\"" . $value['uri'] . "\" title=\"" . $value['descr'] . "\"$selected>" . $value['title'] . "</a>";

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
			echo "$tab<li$selected>$anchor";
		} else {
			echo "</li>\n$tab<li$selected>$anchor";
		}
	} elseif ($level < $value['level']) { // 
		$level = $value['level'];
		echo "\n$tab<ul>\n$tab<li$selected>$anchor";
	} else {
		$cnt = $level - $value['level'];
		while ($cnt--) {
			echo "$tab\t</li>\n$tab\t</ul>\n";
		}
		echo "$tab</li>\n$tab<li$selected>$anchor";
		$level = $value['level'];
	}
	
}

echo "\n</li>\n</ul>\n\n";