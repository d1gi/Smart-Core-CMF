
<div class="default-form">

<fieldset><legend>Группа тэгов</legend>
	<?php $Form = new Helper_Form($data['edit_tags_group_form_data']); echo $Form;?>
</fieldset>


<fieldset><legend>Тэги в группе</legend>
	<table class="admin-table">
	<tr>
		<th>id</th>
		<th>pos</th>
		<th>Название</th>
		<th>Техническое имя</th>
		<th>Кол-во записей</th>
	</tr>
	<?php
	foreach ($data['tags_list'] as $key => $value) {
		echo "<tr>\n";
		echo "\t<td>$key</td>\n";
		echo "\t<td>$value[pos]</td>\n";
		echo "\t<td><a href=\"?edit_tag=$key\">$value[title]</a></td>\n";
		echo "\t<td>$value[name]</td>\n";
		echo "\t<td>$value[items_count]</td>\n";
		echo "</tr>\n";
	}
	
	?>
</table>
</fieldset>

<fieldset><legend>Создать тэг</legend>
	<?php $Form = new Helper_Form($data['create_tag_form_data']); echo $Form;?>
</fieldset>


</div>

<a href="?">&laquo; Назад</a>
