<?php

if (is_array($this->messages)) {
	echo 'При заполении формы, были допущены следующие ошибки:<br /><ul id="error-messages">';
	foreach ($this->messages as $key => $value) {
	   echo "<li>$value</li>\n";
	}
	echo '</ul>';
}

echo "<br />";

$Form = new Helper_Form($this->recover_form_data);
echo $Form;

if ($this->update_password_form_data) {
	echo "<h2>Введите новый пароль</h2>";
	$Form = new Helper_Form($this->update_password_form_data);
	echo $Form;
}

echo $this->send_recover_success;

echo $this->update_password_success;
