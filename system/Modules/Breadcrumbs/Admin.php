<?php 
/**
 * Класс с административными методами.
 * 
 * @version 2012-01-14.0
 */
class Module_Breadcrumbs_Admin extends Module_Breadcrumbs implements Admin_ModuleInterface
{
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		return array (
			'delimiter' => array(
				'label' => 'Разделитель:',
				'type' => 'text',
				'value' => $this->delimiter,
				),
			'hide_if_only_home' => array(
				'label' => 'Скрыть, если выбрана корневая папка:',
				'type' => 'checkbox',
				'value' => $this->hide_if_only_home,
				),
			);
	}
}