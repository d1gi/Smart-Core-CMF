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
 * @version		2012-01-12.0
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
		$class_name = get_called_class();
		
		if (!isset(self::$_instances[$class_name])) {
			$args = func_get_args();
			
			if (func_num_args() > 3) {
				die('SingletonBase constructor support 3 argumens maximum.');
			}
			
			switch (func_num_args()) {
				case 0:
					self::$_instances[$class_name] = new $class_name();
					break;
				case 1:
					self::$_instances[$class_name] = new $class_name($args[0]);
					break;
				case 2:
					self::$_instances[$class_name] = new $class_name($args[0], $args[1]);
					break;
				case 3:
					self::$_instances[$class_name] = new $class_name($args[0], $args[1], $args[2]);
					break;
				default;
			}
		}
		return self::$_instances[$class_name];
	}
}