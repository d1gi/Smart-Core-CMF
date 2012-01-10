<?php

echo "$data[message]";
if (isset($data['rubrics']) and !empty($data['rubrics'])) {
	echo "<ul>\n";
	foreach ($data['rubrics'] as $key => $value) {
		echo "\t<li>$value</li>";
	}
	echo "</ul>\n";
	
} else {
	echo "<br />- нет рубрик.";
}
