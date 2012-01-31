<?php
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
	 */
	protected function init()
	{
		//$this->val = $this->Node->params['val'];
	}
	
	/**
	 * Запуск модуля.
	 */
	public function run($params)
	{
		$this->View->new_module = $this->View->getTpl() . ' under construction.';
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
		//cmf_dump($submit);cmf_dump($pd);exit;	
		switch ($submit) {
			case 'save':
				// 
				break;
			default:
		}
	} 
*/

}