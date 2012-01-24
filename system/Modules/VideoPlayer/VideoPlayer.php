<?php
/**
 * Простой модуль вставки видео роликов.
 * 
 * @package Module
 * @version 2011-07-09.0
 */
class Module_VideoPlayer extends Module
{
	/**
	 * Some value...
	 */
	protected $uri;
	protected $width;
	protected $height;
	
	/**
	 * Конструктор.
	 * 
	 * @return void
	 */
	protected function init()
	{
		$this->Node->setDefaultParams(array(
			'uri'	 => '',
			'width'	 => 320,
			'height' => 240,
			));
	}
	
	/**
	 * Запуск модуля.
	 * 
	 * @return void
	 */
	public function run($parser_data)
	{
		$this->View->uri = $this->uri;
		$this->View->width = $this->width;
		$this->View->height = $this->height;
	}
}