<?php
echo "<h1>Авторизация</h1>";

if ($this->register_link) {
	echo "<a href=\"{$this->register_link}\">Регистрация</a>&nbsp;&nbsp;&nbsp;";
}

if ($this->recover_link) {
	echo "<a href=\"{$this->recover_link}\">Восстановление пароля</a>";
}

$Form = new Helper_Form($this->auth_form_data);
echo $Form;

if ($this->auth_openid_form_data) {
	echo "<h1>Войти используя OpenID</h1>";
	$Form = new Helper_Form($this->auth_openid_form_data);
	echo $Form;
}