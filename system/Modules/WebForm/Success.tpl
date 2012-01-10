<?php

if (isset($data['send_success']) and !empty($data['send_success'])) {
	echo "<div id=\"success-messages\">$data[send_success]</div>";
}
