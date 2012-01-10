<?php
echo "<h3>" . $data['edit_album_form_data']['elements']['pd[title]']['value'] . "</h3>";

$Form = new Helper_Form($data['edit_album_form_data']);
echo $Form;

