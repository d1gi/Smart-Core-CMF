<?php

if (is_array($this->messages)) {
	echo 'При регистрации допущены следующие ошибки:<br /><ul id="error-messages">';
	foreach ($this->messages as $key => $value) {
	   echo "<li>$value</li>\n";
	}
	echo '</ul>';
}

echo "<br />";

$Form = new Helper_Form($this->registration_form_data);
echo $Form;
