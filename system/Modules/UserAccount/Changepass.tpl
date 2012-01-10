<h1>Изменение пароля</h1>

<?php
if (isset($data['messages']) and is_array($data['messages'])) {
	echo 'При вводе данных допущены следующие ошибки:<br /><ul id="error-messages">';
	foreach ($data['messages'] as $key => $value) {
	   echo "<li>$value</li>\n";
	}
	echo '</ul>';
}

if (isset($data['update_password_success'])) {
	echo $data['update_password_success'];
	echo "<br /><br /><a href=\"?\">Вернуться к просмотрю профиля</a>";
} else {
	$Form = new Helper_Form($data['password_form_data']);
	echo $Form;
}

