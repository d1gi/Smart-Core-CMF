<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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
	 * @param
	 * @return
	 */
	public function __getEventsList()
	{
		return $this->__events;
	}
}