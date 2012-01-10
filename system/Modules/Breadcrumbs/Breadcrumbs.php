<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Хлебные крошки (Дублирующая навигация).
 * 
 * @version 2011-12-25.0
 */
class Module_Breadcrumbs extends Module
{
	/**
	 * Разделитель.
	 * @var string
	 */
	protected $delimiter = '&raquo;';
	
	/**
	 * Скрыть "хлебные крошки", если выбрана корневая папка.
	 * @var bool
	 */
	protected $hide_if_only_home = 1;
	
	/**
	 * Конструктор.
	 * 
	 * @return void
	 */
	protected function init()
	{
		$this->delimiter		 = $this->Node->params['delimiter'];
		$this->hide_if_only_home = $this->Node->params['hide_if_only_home'];
	}
	
	/**
	 * Запуск модуля.
	 * 
	 * @return void
	 */
	public function run($parser_data)
	{
		$this->output_data['delimiter'] = $this->delimiter;
		if ($this->hide_if_only_home == 1 and count(&$this->EE->breadcrumbs) == 1) {
			$this->output_data['items'] = array();
		} else {
			$this->output_data['items'] = &$this->EE->breadcrumbs;
		}
	}	
}