<?php
// Шаблон отображения выбранной новости.
echo "\n<div class=\"$data[class_prefix]item\">\n";
foreach ($data['item']['content'] as $key => $value) {
	echo "\t<div class=\"$data[class_prefix]$key\">";
	if ($value['type'] === 'img') {
		echo "<img src=\"$value[value]\" alt=\"\" />";
	} else {
		echo "<b>$value[title]:</b> $value[value]";
	}
	echo "</div>\n";
}
echo "</div>\n";
