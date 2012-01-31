<?php
/**
 * Абстрактный класс для всех плагинов.
 * 
 * @author		Artem Ryzhkov
 * @category	System
 * @package		Kernel
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version		2011-09-15.0
 */
abstract class Plugin extends Controller
{
	/**
	 * Список событий, которые может обрабатывать плугин.
	 */
	protected $__events = null;
	
	/**
	 * NewFunction
	 *
	 * @return
	 */
	public function __getEventsList()
	{
		return $this->__events;
	}
}