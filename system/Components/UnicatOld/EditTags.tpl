
<div class="default-form">

<fieldset><legend>Группы тэгов</legend>
<ul>
<?php
	foreach ($data['tags_groups_list'] as $key => $value) {
		echo "\t<li><a href=\"?edit_tags_group=$key\">$value[title]</a></li>\n";				
	}
?>
</ul>
</fieldset>
<fieldset><legend>Добавить новую группу тэгов</legend>
<?php $Form = new Helper_Form($data['new_tags_group_form_data']); echo $Form;?>
</fieldset>

</div>

<a href="?">&laquo; Назад</a>