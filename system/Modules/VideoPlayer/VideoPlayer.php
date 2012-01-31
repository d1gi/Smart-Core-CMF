<?php
/**
 * Простой модуль вставки видео роликов.
 * 
 * @package Module
 * @version 2011-07-09.0
 */
class Module_VideoPlayer extends Module
{
	protected $uri;
	protected $width;
	protected $height;
	
	/**
	 * Конструктор.
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
	 */
	public function run($params)
	{
		$this->View->uri = $this->uri;
		$this->View->width = $this->width;
		$this->View->height = $this->height;
	}
}