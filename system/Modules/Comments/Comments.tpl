<?php

if (count($this->comments) > 0) {
	echo "<h4>Комментарии:</h4>\n<hr />\n\n";
	foreach ($this->comments as $key => $value) {
		echo "<b>$value[nickname]</b> $value[create_datetime]\n<p>$value[content]</p>\n<hr />\n\n";
	
	}
} else {
//	echo "<h4>Пока не оставлено ни одного комментария</h4>";
}

if ($this->add_comment_form_data) {
	echo "<h4>Оставить комментарий</h4>\n";
	
	$Form = new Helper_Form($this->add_comment_form_data);
	echo $Form;
}
