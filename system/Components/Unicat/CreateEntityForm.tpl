<?php

if (isset($data['create_entity_form_data'])) {
	echo "<h1>Не указан экземпляр каталога</h1>";
	echo "<p>Для продолжения работы с модулем необходимо в свойствах выбрать экземпляр.</p>";
	echo "<p>Также можно создать новый экземпляр, а затем выбрать его.</p>";
//	$Form = new Helper_Form($data['choose_entity']);
//	echo $Form;
//	echo "<h2></h2>";
	$Form = new Helper_Form($data['create_entity_form_data']);
	echo $Form;
}
