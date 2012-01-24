<?php 

foreach ($this->menu as $key => $value) {
	if (isset($value['selected']) and $value['selected'] == true) {
		echo "<strong><a href=\"$value[uri]\">$value[title]</a></strong> | \n";
	} else {
		echo "<a href=\"$value[uri]\">$value[title]</a> | \n";
	}	
}
