<?php

if (isset($data['comments'])) {
	if (count($data['comments']) > 0) {
		echo "<h4>Комментарии:</h4>\n<hr />\n\n";
		foreach ($data['comments'] as $key => $value) {
			echo "<b>$value[nickname]</b> $value[create_datetime]\n<p>$value[content]</p>\n<hr />\n\n";
		
		}
	} else {
		echo "<h4>Пока не оставлено ни одного комментария</h4>";
	}
}

if (isset($data['add_comment_form_data'])) {
	echo "<h4>Оставить комментарий</h4>\n";
	
	$Form = new Helper_Form($data['add_comment_form_data']);
	echo $Form;
}
