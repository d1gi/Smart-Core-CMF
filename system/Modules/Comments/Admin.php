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
		return array(
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
	}
}