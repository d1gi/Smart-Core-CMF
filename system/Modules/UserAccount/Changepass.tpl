<h1>Изменение пароля</h1>

<?php
if (is_array($this->messages)) {
	echo 'При вводе данных допущены следующие ошибки:<br /><ul id="error-messages">';
	foreach ($this->messages as $key => $value) {
	   echo "<li>$value</li>\n";
	}
	echo '</ul>';
}

if ($this->update_password_success) {
	echo $this->update_password_success;
	echo "<br /><br /><a href=\"?\">Вернуться к просмотрю профиля</a>";
} else {
	$Form = new Helper_Form($this->password_form_data);
	echo $Form;
}
