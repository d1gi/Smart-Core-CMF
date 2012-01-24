<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Базовый класс, предоставляющий общие системные данные.
 * 
 * @author		Artem Ryzhkov
 * @category	System
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @uses		DB
 * @uses		Env
 * @uses		Profiler
 * @uses		Registry
 * 
 * @version		2012-01-22.0
 */
abstract class Base
{
	static protected $profiler_enable = false;
	
	/**
	 * Database connection.
	 * 
	 * @access protected
	 * @var object
	 */
	protected $DB;
	
	/**
	 * Constructor.
	 * 
	 * Вызывается как parent::__construct(); из дочерних классов.
	 * 
	 * @access protected
	 */
	protected function __construct()
	{
		$this->DB	= Registry::get('DB');
		$this->Env	= Environment::getInstance();
	}
	
	/**
	 * "Магическое" обращение к системным синглтон классам.
	 *
	 * @param string $class_name
	 * @return Singleton object
	 */
	public function __get($class_name)
	{
		if (class_exists($class_name) and !isset($this->$class_name)) {
			if (method_exists($class_name, 'getInstance')) {
				$this->$class_name = call_user_func($class_name .'::getInstance');
			} else {
				if (! Registry::exists($class_name)) {
					Registry::set($class_name, new $class_name());
				}
				$this->$class_name = Registry::get($class_name);
			}
			
			return $this->$class_name;
		}
		
		if (isset($this->$class_name)) {
			return $this->$class_name;
		}
		
		if ($class_name == 'Env') {
			$this->Env	= Environment::getInstance();
			return $this->Env;
		}
		
		return null;
	}

	/**
	 * Запуск профилировщика.
	 * 
	 * @param string $tag - Тэг, например kernel или node.
	 * @param string $name - Имя ключа, наприме init или номер ноды.
	 */
	public function profilerStart($tag, $name)
	{
		if (self::$profiler_enable === true) {
			Profiler::start($tag, $name);
		}
	}	
			
	/**
	 * Оствновка профилировщика.
	 * 
	 * @param string $tag - Тэг, например kernel или node.
	 * @param string $name - Имя ключа, наприме init или номер ноды.
	 */
	public function profilerStop($tag, $name)
	{
		if (self::$profiler_enable === true) {
			Profiler::stop($tag, $name);
		}
	}			
}