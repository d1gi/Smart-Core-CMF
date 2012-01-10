<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс с административными методами.
 * 
 * @version 2011-08-28.0
 */
class Module_Breadcrumbs_Admin extends Module_Breadcrumbs implements Admin_ModuleInterface
{
	/**
	 * Вызывается при создании ноды.
	 * 
	 * @return array params
	 */
	public function createNode()
	{
		$params = array(
			'delimiter' => '&raquo;',
			'hide_if_only_home' => false,
			);
		return $params;
	}
	
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		$node_params['delimiter'] = array(
			'label' => 'Разделитель:',
			'type' => 'text',
			'value' => $this->delimiter,
			);
		$node_params['hide_if_only_home'] = array(
			'label' => 'Скрыть, если выбрана корневая папка:',
			'type' => 'checkbox',
			'value' => $this->hide_if_only_home,
			);
		return $node_params;
	}
}