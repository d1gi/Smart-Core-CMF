<?php

if ($this->manage_link) {
	echo "<a href=\"" . $this->manage_link . "\">Выпуски рассылок</a> &nbsp;&nbsp; <br /><br />";
}

if ($this->error_messages and is_array($this->error_messages)) {
	echo 'При вводе данных допущены следующие ошибки:<br /><ul id="error-messages">';
	foreach ($this->error_messages as $key => $value) {
	   echo "<li>$value</li>\n";
	}
	echo '</ul>';
}

if ($this->error_message) {
	echo "<div class=\"error_message\">" . $this->error_message . "</div>";
}

if ($this->success_message) {
	echo "<div class=\"success_message\">" . $this->success_message . "</div>";
}

if ($this->notice_message) {
	echo "<div class=\"notice_message\">" . $this->notice_message . "</div>";
}

if (isset($this->items)) {
	echo "\n<div class=\"{$this->css_prefix}list\">\n";
	
	?>
	<table width="100%" border="1" cellspacing="0">
		<tr>
			<th>Тема</th>
			<th>Дата начала рассылки</th>
			<th>Статус</th>
		</tr>
	<?php
	
	foreach ($this->items as $key => $value) {
		echo "<tr>\n";
		echo "\t<td><div class=\"{$this->css_prefix}item\" id=\"{$this->css_prefix}item_id_$key\">{$value['properties']['content']['subject']['value']}</div></td>\n";
		echo "\t<td>" . @$value['properties']['content']['auto_start_datetime']['value'] . "</td>\n";
		echo "\t<td>" . @$value['properties']['content']['status']['value'] . "</td>\n";
		echo "</tr>\n";
	}
	
	?>
	</table>
	<?php
	
	echo "</div>\n";
} 

if (!empty($this->subscribe_form)) {
	$Form = new Helper_Form($this->subscribe_form);
	echo $Form;
}
