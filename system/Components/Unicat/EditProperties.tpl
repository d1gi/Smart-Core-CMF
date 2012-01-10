<h3>Свойства</h3>

<div class="default-form">

<fieldset><legend>Группы свойств</legend>
<ul>
<?php
	foreach ($data['properties_groups_list'] as $key => $value) {
		echo "\t<li><a href=\"?edit_properties_group=$key\">$value[title]</a></li>\n";				
	}
?>
</ul>
</fieldset>

<fieldset><legend>Добавить новую группу свойств</legend>
<?php $Form = new Helper_Form($data['new_properties_group_form_data']); echo $Form;?>
</fieldset>

</div>

<a href="?">&laquo; Назад</a>
