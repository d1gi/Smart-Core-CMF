<?php
echo "<h1>Авторизация</h1>";

if (isset($data['register_link'])) {
	echo "<a href=\"{$data['register_link']}\">Регистрация</a>&nbsp;&nbsp;&nbsp;";
}

if (isset($data['recover_link'])) {
	echo "<a href=\"{$data['recover_link']}\">Восстановление пароля</a>";
}

$Form = new Helper_Form($data['auth_form_data']);

echo $Form;

if (isset($data['auth_openid_form_data'])) {
	echo "<h1>Войти используя OpenID</h1>";
	$Form = new Helper_Form($data['auth_openid_form_data']);
	echo $Form;
}