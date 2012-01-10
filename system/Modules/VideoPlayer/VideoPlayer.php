<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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
		$this->uri = $this->Node->params['uri'];
		$this->width = $this->Node->params['width'];
		$this->height = $this->Node->params['height'];
	}
	
	/**
	 * Запуск модуля.
	 * 
	 * @return void
	 */
	public function run($parser_data)
	{
		$this->output_data['uri'] = $this->uri;
		$this->output_data['width'] = $this->width;
		$this->output_data['height'] = $this->height;
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

}
