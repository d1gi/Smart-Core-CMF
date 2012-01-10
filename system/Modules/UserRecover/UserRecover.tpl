<?php

if (isset($data['messages']) and is_array($data['messages'])) {
	echo 'При заполении формы, были допущены следующие ошибки:<br /><ul id="error-messages">';
	foreach ($data['messages'] as $key => $value) {
	   echo "<li>$value</li>\n";
	}
	echo '</ul>';
}

echo "<br />";

if (isset($data['recover_form_data'])) {
	$Form = new Helper_Form($data['recover_form_data']);
	echo $Form;
}

if (isset($data['update_password_form_data'])) {
	echo "<h2>Введите новый пароль</h2>";
	$Form = new Helper_Form($data['update_password_form_data']);
	echo $Form;
}

if (isset($data['send_recover_success'])) {
	echo $data['send_recover_success'];
}

if (isset($data['update_password_success'])) {
	echo $data['update_password_success'];
}
