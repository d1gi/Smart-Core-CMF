<?php 
/**
 * Простой интерфейс БД.
 * 
 * @uses DB_Simple_*
 * 
 * @version 2011-11-22.0
 */
class DB_Simple
{
	private static $instance;

	/**
	 * Конструктор. Синглтон паттерн.
	 */
	private function __construct(){}

	/**
	 * Синглтон паттерн.
	 */
	public static function getInstance(array $cfg = null, array $options = null) 
	{
		if (!isset(self::$instance)) {
			$classname = 'DB_Simple_' . ucfirst($cfg['db_type']);
			$obj = clone new $classname;
			$obj->connect($cfg);
			return $obj;
		}

		return self::$instance;
	}

	/**
	 * Запрет клонирования. Синглтон паттерн.
	 */
	public function __clone()
	{
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}
}