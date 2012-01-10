<?php

if (isset($data['messages']) and is_array($data['messages'])) {
	echo 'При вводе данных допущены следующие ошибки:<br /><ul id="error-messages">';
	foreach ($data['messages'] as $key => $value) {
	   echo "<li>$value</li>\n";
	}
	echo '</ul>';
}

$Form = new Helper_Form($data['profile_form_data']);
echo $Form;
