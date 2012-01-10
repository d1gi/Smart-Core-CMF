<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Module ???.
 * 
 * @uses 
 * 
 * @package Module
 * @version 2011-07-18.0
 */
class Module_??? extends Module
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
		$this->output_data['new_module'] = $this->getTplName() . ' under construction.';
		//$this->output_data['val'] = $this->val;
	}	

	/**
	 * Парсер части УРИ.
	 * 
	 * @param string $path - часть URI запроса
	 * @return array|false
	 */
/*
	public function parser($path)
	{
		$data = array();
		return $data;
	}
*/
	/**
	 * Обработчик POST данных
	 * 
	 * @param int $pd
	 * @param string $submit
	 * @return void
	 */
/*	public function postProcessor($pd, $submit)
	{
		//cf_debug($submit);cf_debug($pd);exit;	
		switch ($submit) {
			case 'save':
				// 
				break;
			default:
		}
	} 
*/

}
