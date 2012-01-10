<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Базовый класс, предоставляющий общие данные.
 * 
 * @author		Artem Ryzhkov
 * @category	System
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @uses		DB
 * @uses		Env
 * 
 * @version		2011-12-25.0
 */
abstract class Base extends Object
{
	/**
	 * Database connection.
	 * 
	 * @access protected
	 * @var object
	 */
	protected $DB;
	
	/**
	 * System environment.
	 * 
	 * @access protected
	 * @var object
	 */
	protected $Env;
	
	/**
	 * Constructor.
	 * 
	 * Вызывается как parent::__construct(); из дочерних классов.
	 * 
	 * @access protected
	 */
	protected function __construct()
	{
		$this->DB = Kernel::getDBConnection();
		$this->Env = Kernel::getEnv();
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
			$this->$class_name = call_user_func($class_name .'::getInstance');
			return $this->$class_name;
		} else if (isset($this->$class_name)) {
			return $this->$class_name;
		} 
		
		return null;
	}
}