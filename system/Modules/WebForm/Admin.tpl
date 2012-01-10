<?php
/**
 * @version 2011-07-19.0
 */

// Отображение меню.
if (isset($data['menu'])) {
	foreach ($data['menu'] as $key => $value) {
		if (isset($value['selected']) and $value['selected'] == true) {
			echo "<b><a href=\"$value[link]\">$value[title]</a></b> | ";
		} else {
			echo "<a href=\"$value[link]\">$value[title]</a> | ";
		}
	}
	echo "<br /><br />\n\n";
}
 
// Список всех Веб-форм.
if (isset($data['webforms_list']) and count($data['webforms_list'] > 0)) {
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
		foreach ($data['webforms_list'] as $form_id => $value) {
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
if (isset($data['webform_fields']) and count($data['webform_fields']) > 0) {
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
		foreach ($data['webform_fields'] as $field_id => $value) {
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
if (isset($data['result']) and count($data['result']) > 0) {
	?>
	<fieldset><legend>Просмотр результата веб-формы</legend>
	<table class="admin-table" width="100%">
		<?php
		echo "<tr>\n";
		echo "<td>Результат</td><td>";
			foreach ($data['result']['result_data'] as $key => $value) {
				echo "<b>$value[title]</b>: $value[content] <br /><br />";
			}
		echo "</td>";
//		echo "<td>{$data['result']['result_data']}</td>";
		echo "</tr>\n";

		echo "<tr>\n";
		echo "<td>Отправлено</td>";
		echo "<td>{$data['result']['datetime']} (by user_id: {$data['result']['sender_user_id']})</td>";
		echo "</tr>\n";
		
		echo "<tr>\n";
		echo "<td>IP адрес</td>";
		echo "<td>{$data['result']['ip']}</td>";
		echo "</tr>\n";
		
		echo "<tr>\n";
		echo "<td>Браузер</td>";
		echo "<td>{$data['result']['browser']} v{$data['result']['browser_version']} ({$data['result']['platform']})</td>";
		echo "</tr>\n";
		
		echo "<tr>\n";
		echo "<td>User Agent</td>";
		echo "<td>{$data['result']['user_agent']}</td>";
		echo "</tr>\n";
		
		?>
	</table>
	</fieldset>
	<?
}

// Список результатов отправки формы.
if (isset($data['results']) and count($data['results']) > 0) {
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
		foreach ($data['results'] as $field_id => $value) {
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
if (isset($data['create_webform_form_data'])) {
	echo "<fieldset><legend>Создать новую веб-форму</legend>";
	$Form = new Helper_Form($data['create_webform_form_data']);
	echo $Form;
	echo "</fieldset>";
}

// Форма создания поля.
if (isset($data['create_webform_field_form_data'])) {
	echo "<fieldset><legend>Создать поле</legend>";
	$Form = new Helper_Form($data['create_webform_field_form_data']);
	echo $Form;
	echo "</fieldset>";
}

// Форма редактирования поля.
if (isset($data['edit_webform_field_form_data'])) {
	$Form = new Helper_Form($data['edit_webform_field_form_data']);
	echo $Form;
}

// Форма редактирования Веб-формы.
if (isset($data['edit_webform_form_data'])) {
//	echo "<fieldset><legend>Создать новую Веб-форму</legend>";
	$Form = new Helper_Form($data['edit_webform_form_data']);
	echo $Form;
//	echo "</fieldset>";
}

