<?php
// Шаблон отображения записей каталога.

if (isset($data['category_properties']) and !empty($data['category_properties'])) {
	echo "\n<div class=\"unicat_properties\">\n";
	foreach ($data['category_properties'] as $property_name => $property_value) {
		echo "\t<div class=\"unicat_property_$property_name\">$property_value</div>\n";
	}
	echo "</div>\n";
}

if (isset($data['items'])) {
	echo "\n<div class=\"$data[class_prefix]list\">\n";
	foreach ($data['items'] as $key => $value) {
		echo "<div class=\"$data[class_prefix]item\" id=\"$data[class_prefix]item_id_$key\">\n";
		foreach ($value['properties']['content'] as $prop_name => $val) {
			echo "\t<div class=\"prop_$prop_name\">";
			if ($val['type'] === 'img') {
				echo "<img src=\"$val[value]\" alt=\"\" />";
			} else {
				echo "<b>$val[title]:</b> $val[value]";
			}
			echo "</div>\n";
		}
		
		/*
		if (count($value['properties']['tags']) > 0) {
			echo "\t<div class=\"tags_list\">Тэги:&nbsp;";
			foreach ($value['properties']['tags'] as $tag) {
				echo "<a href=\"$tag[link]\">$tag[title]</a>, ";
			}
			echo "</div>\n";
		}
		*/
		
		if (isset($value['link'])) {
			echo "\t<div class=\"link\"><a href=\"$value[link]\">Подробнее...</a></div>\n";
		}
		echo "\t<div class=\"clear\"></div>\n</div>\n";
	}
	echo "</div>\n";
} 
?>

<div class="paginator">
<?php echo @$data['pages'];?>
</div>
