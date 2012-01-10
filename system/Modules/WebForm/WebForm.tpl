<?php
/**
 * @version 2011-07-19.0
 */

// Вывод сообщений.
if (isset($data['messages']) and is_array($data['messages'])) {
	echo 'При заполнении формы допущены следующие ошибки:<br /><ul id="error-messages">';
	foreach ($data['messages'] as $key => $value) {
	   echo "<li>$value</li>\n";
	}
	echo '</ul>';
}

// Сама форма.
if (isset($data['webform'])) {
	$Form = new Helper_Form($data['webform']);
	echo $Form;
}
