<?php
/**
 * Класс для реализации Синглтон паттерна.
 * 
 * @author		Artem Ryzhkov
 * @category	System
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version		2012-01-12.0
 */
abstract class Singleton
{
	private static $_instances = array();
	protected function __construct() {}
	protected function __clone() {}
	protected function __wakeup() {}

	public static function getInstance()
	{
		$class_name = get_called_class();
		
		if (!isset(self::$_instances[$class_name])) {
			$args = func_get_args();
			
			if (func_num_args() > 3) {
				die('Singleton constructor support 3 argumens maximum.');
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