<?php
//echo $data['text'] . "\n";

foreach ($data['albums'] as $key => $value) {
	echo "<div class=\"gallery_album\" id=\"news_item_id_$key\">";
	echo "\t<table class=\"design\"><tr><td><a href=\"?album=$key\" class=\"gallery_image\"><img src=\"$value[thumbnail_link]\" alt=\"\"></a></td><td>\n";
	
	echo "\t<a href=\"?album=$key\" class=\"gallery_album_title\">$value[title]</a>";
	echo "\t<p>$value[count] фотографий</p>";
	
	if (!empty($value['descr'])) {
		echo "\t<p class=\"gallery_album_descr\">$value[descr]</p>";
	}
	
	echo "\t<p>Обновлен $value[last_update_datetime]</p>"; // , создан $value[create_datetime]
	
	echo "\t</td></tr></table>";
	
	echo "</div>";
//	echo "<div class=\"clear\"></div>";
}
