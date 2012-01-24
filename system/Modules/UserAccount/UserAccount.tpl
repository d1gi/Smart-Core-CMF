<?php
//echo $this->welcome_text . ', <b>' . $this->name . '</b>';
echo '<h1>Персональные данные</h1>';
echo "<table>\n";
echo "</td></tr>\n<tr><td>Псевдоним:</td><td><b>{$this->user_data['nickname']}</b></td></tr>";
echo "<tr><td width=\"180\">Текущий логин:</td><td>{$this->user_data['login']}";

if ($this->password !== false) {
	echo " (<a href=\"{$this->password['change_link']}\">{$this->password['title']}</a>)";
}

echo "<tr><td>Полное имя:</td><td>{$this->user_data['fullname']}</td></tr>";
echo "<tr><td>Адрес e-mail:</td><td>{$this->user_data['email']}</td></tr>";

echo "<tr><td>Пол:</td><td>" ;
if (strtoupper($this->user_data['gender']) == 'M') {
	echo "мужской";
} else if (strtoupper($this->user_data['gender']) == 'F') {
	echo "женский";
}
echo "</td></tr>";

echo "<tr><td>Дата рождения:</td><td>{$this->user_data['dob']}</td></tr>";
echo "<tr><td>Язык:</td><td>{$this->user_data['language']}</td></tr>";
echo "<tr><td>Часовой пояс:</td><td>{$this->user_data['timezone']}</td></tr>";
echo "</table>\n";

echo "<br /><a href=\"{$this->changereg_link}\">Изменить персональные данные</a>";
echo "<br /><br /><img src=\"http://img.imgsmail.ru/r/openid/favicon.ico\" > <a href=\"#\">Мои логины (OpenID)</a>";

//echo "<br /><br /><a href=\"{$this->logout['link']}\">{$this->logout['title']}</a>";
?>

<br /><br />
<form action="<?php echo $this->logout['link']?>" method="get">
<input type="hidden" name="<?php echo $this->logout['form_element']?>" />
<input type="submit" value="<?php echo $this->logout['title']?>" />
</form>
