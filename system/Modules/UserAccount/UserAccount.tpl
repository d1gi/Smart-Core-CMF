<?php
//echo $data['welcome_text'] . ', <b>' . $data['name'] . '</b>';
echo '<h1>Персональные данные</h1>';
echo "<table>\n";
echo "</td></tr>\n<tr><td>Псевдоним:</td><td><b>{$data['user_data']['nickname']}</b></td></tr>";
echo "<tr><td width=\"180\">Текущий логин:</td><td>{$data['user_data']['login']}";

if ($data['password'] !== false) {
	echo " (<a href=\"{$data['password']['change_link']}\">{$data['password']['title']}</a>)";
}

echo "<tr><td>Полное имя:</td><td>{$data['user_data']['fullname']}</td></tr>";
echo "<tr><td>Адрес e-mail:</td><td>{$data['user_data']['email']}</td></tr>";

echo "<tr><td>Пол:</td><td>" ;
if (strtoupper($data['user_data']['gender']) == 'M') {
	echo "мужской";
} else if (strtoupper($data['user_data']['gender']) == 'F') {
	echo "женский";
}
echo "</td></tr>";

echo "<tr><td>Дата рождения:</td><td>{$data['user_data']['dob']}</td></tr>";
echo "<tr><td>Язык:</td><td>{$data['user_data']['language']}</td></tr>";
echo "<tr><td>Часовой пояс:</td><td>{$data['user_data']['timezone']}</td></tr>";
echo "</table>\n";

echo "<br /><a href=\"$data[changereg_link]\">Изменить персональные данные</a>";
echo "<br /><br /><img src=\"http://img.imgsmail.ru/r/openid/favicon.ico\" > <a href=\"#\">Мои логины (OpenID)</a>";

//echo "<br /><br /><a href=\"{$data['logout']['link']}\">{$data['logout']['title']}</a>";
?>

<br /><br />
<form action="<?php echo $data['logout']['link']?>" method="get">
<input type="hidden" name="<?php echo $data['logout']['form_element']?>" />
<input type="submit" value="<?php echo $data['logout']['title']?>" />
</form>