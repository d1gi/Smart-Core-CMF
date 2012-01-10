<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Module ???.
 * 
 * @uses 
 * 
 * @package Module
 * @version 2011-00-00.0
 */
class Module_TestHook extends Module
{
	/**
	 * Some value...
	 */
	protected $val;
	
	/**
	 * Конструктор.
	 * 
	 * @return void
	 */
	protected function init()
	{
		//$this->val = $this->Node->params['val'];
	}
	
	/**
	 * Запуск модуля.
	 * 
	 * @return void
	 */
	public function run($parser_data)
	{
		$Node = new Node();
		/*
		// Проверка текстера:
		$Node->activate(34);
		$args = array('id' => 2);
//		$this->output_data['hook_data'] = $Node->hook('getText', $args);
		$this->output_data['hook_data'] = $Node->hook('getText');
		
		// Проверка каталога.
		$Node->activate(48);
		$args = array(
			'category_id' => 9,
			);
		$this->output_data['hook_data'] = $Node->hook('getCategoriesTree', $args);
//		$this->output_data['hook_data'] = $Node->hook('getCategoriesTree');
		*/
	}	

	/**
	 * Обработчик POST данных
	 * 
	 * @param int $pd
	 * @param string $submit
	 * @return void
	 */
/*	public function postProcessor($pd, $submit)
	{
		switch ($submit) {
			case 'save':
				// 
				break;
			default:
		}
	} 
*/
	
	/**
	 * Получить элементы управления нодой.
	 * 
	 * @return array
	 */
/*	public function getFrontControls()
	{
				
		$this->default_action = 'edit';

		$items['edit'] = array(
			'popup_window_title' => 'Редактирование',
			'title' => 'Редактировать',
			'link' => $this->Env->current_folder_path . ACTION . '/' . $this->Node->id . '/edit/',
			'ico' => 'edit',
			);
		return $items;
		
	}
*/
	
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
/*	public function getParams()
	{
		$node_params['val'] = array(
			'label' => 'Some val',
			'type' => 'text',
			'value' => $this->val
			);
		return $node_params;
	}
*/
	/**
	 * Вызывается при создании ноды.
	 * 
	 * @return array params
	 * 
	 * @todo мультиязычность.
	 */
/*	public function createNode()
	{
		$params = array();
		$params['val'] = 1;
		return $params;
	}
*/
	
}
