<?php
/**
 * @version 2011-07-18.1
 */
 
// Редактирование текстера
if ($this->edit_form_data) {
	$Form = new Helper_Form($this->edit_form_data);
	echo $Form;
	?><div style="width: 100%; text-align: right; margin-top: -40px;"><a href="../meta/">Мета-данные</a>&nbsp;|&nbsp;<a href="javascript:toggletinyMCE('id-pd-text-0');">Вкл/Выкл визуальный редактор</a></div><?php
}

// Упраление мета-данными.
if ($this->meta_controls) {
	$Meta = new Component_Meta();
	$Meta->renderControls($this->meta_controls);
	
	?><div style="width: 100%; text-align: right; margin-top: -40px;"><a href="../edit/">Редактировать текст</a></div><?php
}
