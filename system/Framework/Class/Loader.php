<?php
/**
 * Загрузчик классов.
 * 
 * @author		Artem Ryzhkov
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version 	2012-01-31.0
 */
class Class_Loader
{
	/**
	 * Массив с пространствами имён в изолированных папках.
	 * 
	 * Есть 2 алгоритма подгрузки:
	 *  - Zend, когда например класс Zend_Config_Yaml подгружается из файла Zend/Config/Yaml.php
	 *  - Smart Core, класс Module_Texter_Admin, загружается из файла Module/Texter/Admin.php
	 *                в тоже время класс Module_Texter, загружается из файла Module/Texter/Texter.php
	 *                т.е. пространство имён распространяется еще и на изоляцию внутри папки.
	 * @var array
	 */
	static protected $__namespaces_isolated = array();

	/**
	 * Массив с пространствами имён для автозагрузки классов.
	 * @var array
	 */
	static private $__namespaces = array();
	
	/**
	 * Храние истории всех подгруженных классов.
	 */
	static private $__store = array();
	
	/**
	 * Регистрация пространств имён для автозагрузки классов.
	 * 
	 * @param array $ns
	 * @return void
	 */
	static public function registerNamespace($ns)
	{
		foreach ($ns as $class_prefix => $paths) {
			foreach ($paths as $path) {
				self::$__namespaces[$class_prefix][] = $path;
			}
		}
	}
	
	/**
	 * Регистрация функции автозагрузки классов.
	 * 
	 * @return void
	 */
	static public function registerAutoload()
	{
		spl_autoload_register(array(__CLASS__, 'autoload'));
	}
	
	/**
	 * Добавить изолированные простарнства имён.
	 * 
	 * @param mixed $val
	 * @return void
	 */
	static public function registerNamespaceIsolated($val)
	{
		if (is_array($val)) {
			foreach ($val as $key => $ns) {
				self::$__namespaces_isolated[$ns] = true;
			}
		} else {
			self::$__namespaces_isolated[$val] = true;
		}
	}
	
	/**
	 * Автозагрузка файлов классов. 
	 * 
	 * @param string $class
	 */
	static public function autoload($class)
	{
		$file_path = str_replace('_', '/', $class) . '.php';
		
		// Поиск класса в неймспейсах.
		foreach (self::$__namespaces as $class_prefix => $paths) {
			if (strpos($class, $class_prefix . '_') === 0) {
				
				// Класс является изолированным внутри своей папки.
				if (array_key_exists($class_prefix, self::$__namespaces_isolated)) {
					$class_path_parts = explode('_', $class);
					unset($class_path_parts[0]);
					if (count($class_path_parts) == 1) {
						$file_path = $class_path_parts[1] . '/' . str_replace($class_prefix . '/', '', $file_path);
					} else {
						$file_path = str_replace($class_prefix . '/', '', $file_path);
					}
				}
				
				foreach ($paths as $path) {
					if (file_exists($path . $file_path)) {
						require_once $path . $file_path;
						self::$__store[$class] = $path . $file_path;
						return true;
					}
				}
				break;
			}
		}
		
		// Загрузка класса из путей по умолчанию.
		foreach (self::$__namespaces['*'] as $path) {
			if (file_exists($path . $file_path)) {
				require_once $path . $file_path;
				self::$__store[$class] = $path . $file_path;
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Получение информации.
	 * 
	 * @return array
	 */
	static public function getInfo()
	{
		return array(
			'namespaces' => self::$__namespaces,
			'namespaces_isolated' => self::$__namespaces_isolated,
			'store_count' => count(self::$__store),
			'store' => self::$__store,
			);
	}
}