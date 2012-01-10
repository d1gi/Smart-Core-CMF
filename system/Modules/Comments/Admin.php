<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс с административными методами.
 * 
 * @version 2011-06-29.0
 */
class Module_Comments_Admin extends Module_Comments implements Admin_ModuleInterface
{
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array $node_params
	 */
	public function getParams()
	{
		$node_params = array(
			'source_node_id' => array(
				'label' => 'Источник Node ID:',
				'type' => 'text',
				'value' => $this->source_node_id,
				),
			'is_only_authorized' => array(
				'label' => 'Только для авторизованных пользователей:',
				'type' => 'checkbox',
				'value' => $this->is_only_authorized,
				),
			);
		return $node_params;
	}

	/**
	 * Вызывается при создании ноды.
	 * 
	 * @return array $params
	 */
	public function createNode()
	{
		$params = array(
			'source_node_id' => 0,
			'is_only_authorized' => 1,
			);
		return $params;
	}

}