<?php 
/**
 * Класс с административными методами.
 * 
 * @version 2011-11-01.0
 */
class Module_Reflex_Admin extends Module_Reflex implements Admin_ModuleInterface
{
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		return array(
			'hook_node_id' => array(
				'label' => 'ID ноды',
				'type' => 'string',
				'value' => $this->Node->getParam('hook_node_id')
				),
			'hook_method' => array(
				'label' => 'Имя хука',
				'type' => 'string',
				'value' => $this->Node->getParam('hook_method')
				),
			'hook_args' => array(
				'label' => 'Аргументы (YAML)',
				'type' => 'textarea',
				'value' => $this->Node->getParam('hook_args'),
				),
			'hook_tpl' => array(
				'label' => 'Шаблон',
				'type' => 'string',
				'value' => $this->Node->getParam('hook_tpl')
				),
			'hook_output_data_key' => array(
				'label' => 'output_data_key',
				'type' => 'string',
				'value' => $this->Node->getParam('hook_output_data_key')
				),
			);
	}
}