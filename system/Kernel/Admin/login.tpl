<?php
echo "<div class=\"login-message\">{$data['welcome_message']}</div>";
$Form = new Helper_Form($data['login_form']);
echo $Form;
