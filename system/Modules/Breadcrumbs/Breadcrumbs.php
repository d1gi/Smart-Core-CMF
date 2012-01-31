<?php
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
	protected $delimiter;
	
	/**
	 * Скрыть "хлебные крошки", если выбрана корневая папка.
	 * @var bool
	 */
	protected $hide_if_only_home;
	
	/**
	 * Конструктор.
	 * 
	 * @return void
	 */
	protected function init()
	{
		$this->Node->setDefaultParams(array(
			'delimiter'			=> '&raquo;',
			'hide_if_only_home'	=> false,
			));
		$this->delimiter		 = $this->Node->getParam('delimiter');
		$this->hide_if_only_home = $this->Node->getParam('hide_if_only_home');
	}
	
	/**
	 * Запуск модуля.
	 * 
	 * @return void
	 */
	public function run($params)
	{
		$this->View->delimiter = $this->delimiter;
		$this->View->items = array();
		
		if ($this->hide_if_only_home == false or count($this->Breadcrumbs->get()) > 1) {
			$this->View->items = $this->Breadcrumbs->get();
		}		
	}	
}