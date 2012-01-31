<?php
/**
 * Паттерн Registry.
 * 
 * @author		Artem Ryzhkov
 * @category	System
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version		2012-01-31.0
 */ 
class Registry
{
	/**
	 * Hash table.
	 * @var array
	 */
	private static $registry = array(); 
 
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	/**
	 * Check for key exists.
	 *
	 * @param string $name
	 * @return bool
	 */
	public static function exists($key) 
	{
		return isset(self::$registry[$key]) ? true : false;
	}

	/**
	 * Save an object by key into registry.
	 * 
	 * @param integer|string $key
	 * @param object $object
	 * @return bool
	 */
	static public function set($key, $object)
	{
		if (self::exists($key)) {
			return false;
		} else {
			self::$registry[$key] = $object;
			return true;
		}
	}
 
	/**
	 * Get an object by key from registry.
	 * 
	 * @param integer|string $key
	 * @param bool $try_auto_create - Попробовать автоматически создать объект с именем ключа.
	 * @return object|null
	 */
	static public function get($key, $try_auto_create = true)
	{
		if ((isset(self::$registry[$key]))) {
			return self::$registry[$key];
		} else {
			if ($try_auto_create and class_exists($key)) {
			self::$registry[$key] = method_exists($key, 'getInstance') ? call_user_func($key .'::getInstance') : new $key();
			return self::$registry[$key];
			} else {
				return null;
			}
		}
	}
	
	/**
	 * Получение информации.
	 * 
	 * @return array
	 */
	static public function getInfo()
	{
		$output = array();
		foreach (self::$registry as $key => $_dummy) {
			$output[] = $key;
		}
		return $output;
	}
}