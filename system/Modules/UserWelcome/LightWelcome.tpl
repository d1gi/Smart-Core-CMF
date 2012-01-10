<?php
echo $data['welcome_text'] . ', <b><a href="' . $data['welcome_link'] . '">' . $data['name'] . '</a></b>';
echo "<br /><a href=\"{$data['logout_link']}\">Выход</a>";
