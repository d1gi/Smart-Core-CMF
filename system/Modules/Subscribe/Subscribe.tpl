<?php

if (isset($data['manage_link'])) {
	echo "<a href=\"" . $data['manage_link'] . "\">Выпуски рассылок</a> &nbsp;&nbsp; <br /><br />";
}

if (isset($data['error_messages']) and is_array($data['error_messages'])) {
	echo 'При вводе данных допущены следующие ошибки:<br /><ul id="error-messages">';
	foreach ($data['error_messages'] as $key => $value) {
	   echo "<li>$value</li>\n";
	}
	echo '</ul>';
}

if (isset($data['error_message'])) {
	echo "<div class=\"error_message\">" . $data['error_message'] . "</div>";
}

if (isset($data['success_message'])) {
	echo "<div class=\"success_message\">" . $data['success_message'] . "</div>";
}

if (isset($data['notice_message'])) {
	echo "<div class=\"notice_message\">" . $data['notice_message'] . "</div>";
}

if (isset($data['items'])) {
	echo "\n<div class=\"$data[css_prefix]list\">\n";
	
	?>
	<table width="100%" border="1" cellspacing="0">
		<tr>
			<th>Тема</th>
			<th>Дата начала рассылки</th>
			<th>Статус</th>
		</tr>
	<?php
	
	foreach ($data['items'] as $key => $value) {
		echo "<tr>\n";
		echo "\t<td><div class=\"$data[css_prefix]item\" id=\"$data[css_prefix]item_id_$key\">{$value['properties']['content']['subject']['value']}</div></td>\n";
		echo "\t<td>" . @$value['properties']['content']['auto_start_datetime']['value'] . "</td>\n";
		echo "\t<td>" . @$value['properties']['content']['status']['value'] . "</td>\n";
		echo "</tr>\n";
	}
	
	?>
	</table>
	<?php
	
	echo "</div>\n";
} 

if (isset($data['subscribe_form'])) {
	$Form = new Helper_Form($data['subscribe_form']);
	echo $Form;
}
