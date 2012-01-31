<?php 
/**
 * Класс с административными методами.
 * 
 * @version 2011-09-03.0
 */
class Module_Taxonomy_Admin extends Module_Taxonomy implements Admin_ModuleInterface
{
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		return array(
			'items_per_page' => array(
				'label' => 'Записей на страницу:',
				'type' => 'text',
				'value' => $this->items_per_page,
				),
			'catalog_node_id' => array(
				'label' => 'catalog_node_id',
				'type' => 'text',
				'value' => $this->catalog_node_id,
				),
			'class_prefix' => array(
				'label' => 'Префикс CSS классов:',
				'type' => 'text',
				'value' => $this->class_prefix,
				),
			);
	}

	/**
	 * Вызывается при создании ноды.
	 * 
	 * @return array $params
	 */
	public function createNode()
	{
		$params = array(
			'items_per_page' => 10,
			'catalog_node_id' => 0,
			'class_prefix'	 => 'unicat_',
//			'tpl' => '',
			);
		return $params;
	}
}