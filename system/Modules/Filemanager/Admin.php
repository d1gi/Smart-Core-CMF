<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс с административными методами.
 * 
 * @version 2011-06-29.0
 */
class Module_Filemanager_Admin extends Module_Filemanager implements Admin_ModuleInterface
{
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		$node_params['filemanager_id'] = array(
			'label' => 'Конфигурация файлового менеджера',
			'type' => 'text',
			'value' => $this->filemanager_id
			);
		return $node_params;
	}

	/**
	 * Вызывается при создании ноды.
	 * 
	 * @return array params
	 */
	public function createNode()
	{
		$params = array(
			'filemanager_id' => 0,
			);
		return $params;
	}
}