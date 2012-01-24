<?php
// Шаблон отображения выбранной записи.
echo "\n<div class=\"{$this->class_prefix}item\">\n";
foreach ($this->item['content'] as $key => $value) {
	echo "\t<div class=\"{$this->class_prefix}$key\">";
	if ($value['type'] === 'img') {
		echo "<img src=\"$value[value]\" alt=\"\" />";
	} else {
		echo "<b>$value[title]:</b> $value[value]";
	}
	echo "</div>\n";
}
echo "</div>\n";
