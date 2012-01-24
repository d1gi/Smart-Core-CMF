<h1>Редактирование персональных данных</h1>

<?php
if (is_array($this->messages)) {
	echo 'При вводе данных допущены следующие ошибки:<br /><ul id="error-messages">';
	foreach ($this->messages as $key => $value) {
	   echo "<li>$value</li>\n";
	}
	echo '</ul>';
}

$Form = new Helper_Form($this->personal_form_data);
echo $Form;