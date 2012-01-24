<?php
echo "<h3>" . $this->edit_album_form_data['elements']['pd[title]']['value'] . "</h3>";

$Form = new Helper_Form($this->edit_album_form_data);
echo $Form;

