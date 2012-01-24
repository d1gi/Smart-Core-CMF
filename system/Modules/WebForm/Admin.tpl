<?php
/**
 * @version 2012-01-24.0
 */

// Список всех Веб-форм.
if (is_array($this->webforms_list) and count($this->webforms_list > 0)) {
	?>
	<fieldset><legend>Список всех веб-форм</legend>
	<table class="admin-table" width="100%">
		<tr>
			<th width="1px">ID</th>
			<th>Name</th>
			<th>Title</th>
			<th>Fields</th>
			<th>Messages</th>
			<th>Action</th>
		</tr>        
		<?php
		foreach ($this->webforms_list as $form_id => $value) {
			echo "<tr>\n";
			echo "<td>$form_id</td>";
			echo "<td><a href=\"$form_id/\">$value[name]</a></td>";
			echo "<td><a href=\"$form_id/\">$value[title]</a></td>";
			echo "<td>$value[fields_count]</td>";
			echo "<td>$value[results_count]</td>";
			
			if ($value['fields_count'] == 0) {
				echo "<td><a href=\"?del_form=$form_id\" onclick=\"return confirm('Вы уверены, что хотите удалить веб-форму: $value[title]?')\">Удалить</a></td>\n";
			} else {
				echo "<td>Удалить</td>\n";
			}
			
			echo "</tr>\n";
		}
		?>
	</table>
	</fieldset>
	<?
}

// Список полей формы.
if (is_array($this->webform_fields) and count($this->webform_fields) > 0) {
	?>
	<fieldset><legend>Поля веб-формы</legend>
	<table class="admin-table" width="100%">
		<tr>
			<th width="1px">ID</th>
			<th width="1px">Pos</th>
			<th width="1px">Active</th>
			<th>Title</th>
			<th>Name</th>
			<th>Required</th>
			<th>Type</th>
			<th>Service Comment</th>
<!--			<th>Action</th>-->
		</tr>        
		<?php
		foreach ($this->webform_fields as $field_id => $value) {
			echo "<tr>\n";
			echo "<td>$field_id</td>";
			echo "<td>$value[pos]</td>";
			echo "<td>$value[is_active]</td>";
			echo "<td><a href=\"$field_id/\">$value[title]</a></td>";
			echo "<td><a href=\"$field_id/\">$value[name]</a></td>";
			echo "<td>$value[is_required]</td>";
			echo "<td>$value[type]</td>";
			echo "<td>$value[service_comment]</td>";
//			echo "<td><a href=\"?del_field=$field_id\" onclick=\"return confirm('Вы уверены, что хотите удалить веб-форму: $value[title]?')\">Удалить</a></td>\n";
			echo "</tr>\n";
		}
		?>
	</table>
	</fieldset>
	<?
}

// Просмотр результата.
if (is_array($this->result) and count($this->result) > 0) {
	?>
	<fieldset><legend>Просмотр результата веб-формы</legend>
	<table class="admin-table" width="100%">
		<?php
		echo "<tr>\n";
		echo "<td>Результат</td><td>";
			foreach ($this->result['result_data'] as $key => $value) {
				echo "<b>$value[title]</b>: $value[content] <br /><br />";
			}
		echo "</td>";
//		echo "<td>{$this->result['result_data']}</td>";
		echo "</tr>\n";

		echo "<tr>\n";
		echo "<td>Отправлено</td>";
		echo "<td>{$this->result['datetime']} (by user_id: {$this->result['sender_user_id']})</td>";
		echo "</tr>\n";
		
		echo "<tr>\n";
		echo "<td>IP адрес</td>";
		echo "<td>{$this->result['ip']}</td>";
		echo "</tr>\n";
		
		echo "<tr>\n";
		echo "<td>Браузер</td>";
		echo "<td>{$this->result['browser']} v{$this->result['browser_version']} ({$this->result['platform']})</td>";
		echo "</tr>\n";
		
		echo "<tr>\n";
		echo "<td>User Agent</td>";
		echo "<td>{$this->result['user_agent']}</td>";
		echo "</tr>\n";
		
		?>
	</table>
	</fieldset>
	<?
}

// Список результатов отправки формы.
if (isset($this->results) and count($this->results) > 0) {
	?>
	<fieldset><legend>Результаты отправки веб-формы</legend>
	<table class="admin-table" width="100%">
		<tr>
			<th width="1px">ID</th>
			<th>Datetime</th>
			<th>Sender User</th>
			<th>IP address</th>
			<!--<th>Action</th>-->
		</tr>        
		<?php
		foreach ($this->results as $field_id => $value) {
			echo "<tr>\n";
			echo "<td>$field_id</td>";
			echo "<td><a href=\"$field_id/\">$value[datetime]</a></td>";
			echo "<td>$value[sender_user_id]</td>";
			echo "<td><a href=\"$field_id/\">$value[ip]</a></td>";
//			echo "<td><a href=\"?del_field=$field_id\" onclick=\"return confirm('Вы уверены, что хотите удалить веб-форму: $value[title]?')\">Удалить</a></td>\n";
			echo "</tr>\n";
		}
		?>
	</table>
	</fieldset>
	<?
}
 
// Форма создания новой Веб-формы.
if ($this->create_webform_form_data) {
	echo "<fieldset><legend>Создать новую веб-форму</legend>";
	$Form = new Helper_Form($this->create_webform_form_data);
	echo $Form;
	echo "</fieldset>";
}

// Форма создания поля.
if ($this->create_webform_field_form_data) {
	echo "<fieldset><legend>Создать поле</legend>";
	$Form = new Helper_Form($this->create_webform_field_form_data);
	echo $Form;
	echo "</fieldset>";
}

// Форма редактирования поля.
$Form = new Helper_Form($this->edit_webform_field_form_data);
echo $Form;

// Форма редактирования Веб-формы.
//if ($this->edit_webform_form_data) {
//	echo "<fieldset><legend>Создать новую Веб-форму</legend>";
	$Form = new Helper_Form($this->edit_webform_form_data);
	echo $Form;
//	echo "</fieldset>";
//}

