<?php
/**
 * @version 2011-07-19.0
 */

// Вывод сообщений.
if (is_array($this->messages)) {
	echo 'При заполнении формы допущены следующие ошибки:<br /><ul id="error-messages">';
	foreach ($this->messages as $key => $value) {
	   echo "<li>$value</li>\n";
	}
	echo '</ul>';
}

// Сама форма.
if ($this->webform) {
	$Form = new Helper_Form($this->webform);
	echo $Form;
}
