<?php

if ($data['login_link'] !== false ) {
	echo "<a href=\"{$data['login_link']}\">Авторизация</a>";
}

if ($data['register_link'] !== false ) {
	echo " | <a href=\"{$data['register_link']}\">Регистрация</a>";
}
