<?php

if ($this->login_link) {
	echo "<a href=\"{$this->login_link}\">Авторизация</a>";
}

if ($this->register_link) {
	echo " | <a href=\"{$this->register_link}\">Регистрация</a>";
}
