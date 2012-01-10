<?php

if (isset($data['messages']) and is_array($data['messages'])) {
	echo 'При регистрации допущены следующие ошибки:<br /><ul id="error-messages">';
	foreach ($data['messages'] as $key => $value) {
	   echo "<li>$value</li>\n";
	}
	echo '</ul>';
}

echo "<br />";

$Form = new Helper_Form($data['registration_form_data']);
echo $Form;
