<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс для реализации Синглтон паттерна с унаследованным классом Base.
 * 
 * Является аналогом класса Singleton, за исключением того, что в конструкторе вызывается конструктор класса Base.
 * 
 * @author		Artem Ryzhkov
 * @category	System
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version		2011-12-25.0
 */
abstract class SingletonBase extends Base
{
	private static $_instances = array();
	protected function __construct() {
		parent::__construct();
	}
	private function __clone() {}
	private function __wakeup() {}

	public static function getInstance()
	{
		$class = get_called_class();
		if (!isset(self::$_instances[$class])) {
			if (func_num_args() == 0) {
				self::$_instances[$class] = new $class();
			} else {
				$cnt = func_num_args();
				
				$args = '';
				foreach (func_get_args() as $value) {
					// @todo объекты пока нельзя передать как аргумент.
					if (is_object($value)) {
						die ('SingletonBase does not support object as constrictor argument, yet.');
					}
					// Числа добавляются в строку аргументов без экранирования.
					elseif (is_numeric($value)) {
						$args .= $value;
					}
					// Массивы и строки преобразовываются в PHP код.
					else {
						ob_start();
						ob_implicit_flush(false);
						var_export($value);
						$args .= ob_get_clean();
					}
					
					if (--$cnt !== 0) {
						$args .= ', ';
					}
				}
				eval('self::$_instances[$class] = new $class(' . $args . ');');
			}
		}
		return self::$_instances[$class];
	}	
}