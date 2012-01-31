<?php
/**
 * Smart Core CMF
 * 
 * Работа с базами данных.
 * 
 * @author		Artem Ryzhkov
 * @package		Kernel
 * @subpackage	Database
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version 	2012-01-31.0
 */
class DB
{
	/**
	 * Indicates the current default fetch mode should be used
	 */
	const FETCHMODE_DEFAULT = 0;

	/**
	 * Column data indexed by column names
	 */
	const FETCHMODE_ASSOC = 1;

	/**
	 * Column data as object properties
	 */
	const FETCHMODE_OBJECT = 2;

	/**
	 * Счетчик кол-ва запросов.
	 * @var int
	 */
	static private $_query_count = 0;
	
	/**
	 * Стек всех выполненных запросов.
	 * @var array
	 */
	static public $query_log = array();
	
	/**
	 * Время выполнения каждого запроса в стеке.
	 * @var array
	 */
	static public $query_log_profiler = array();
	
	/**
	 * Constructor.
	 */
	private function __construct() {}
	
	/**
	 * Подключение к БД.
	 * 
	 * @param array $cfg
	 * @return db resource|void
	 */
	static public function connect(array $cfg = null)
	{
		// Выполнение подключения.
		switch ($cfg['db_lib']) {
			case 'PDO':
				$driver_options = array (
					PDO::ATTR_PERSISTENT => $cfg['db_persist'],
					PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
//					PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
					PDO::ATTR_EMULATE_PREPARES => true,
					);
				if ($cfg['db_type'] == 'mysqli') {
					$cfg['db_type'] = 'mysql';
				}
				if ($cfg['db_type'] == 'mysql') {
					$driver_options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8';
				}
				try {
					$conn = new DB_PDO($cfg['db_type'] . ':host=' . $cfg['db_host'] . ';port=' . $cfg['db_port'] . ';dbname=' . $cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $driver_options);
					unset($driver_options);
				} catch (Exception $e) {
					Log::put($e->getMessage() . "\n\n" . $e->getTraceAsString(), 'db/');
					die('Database connection error');
				}
				break;
			case 'Simple':
				$conn = DB_Simple::getInstance(array(
					'db_type' => $cfg['db_type'],
					'db_host' => $cfg['db_host'],
					'db_port' => $cfg['db_port'],
					'db_user' => $cfg['db_user'],
					'db_pass' => $cfg['db_pass'],
					'db_name' => $cfg['db_name']),
					array('debug' => 0, 'portability' => 1)
					);
				break;
			case 'Zend':
				$conn = new DB_Zend(array(
					'host'		=> $cfg['db_host'],
					'username'	=> $cfg['db_user'],
					'password'	=> $cfg['db_pass'],
					'dbname'	=> $cfg['db_name'],
					'port'		=> $cfg['db_port'],
				));
				break;
			default:
		}
		
		// Установка префикса таблиц.
		if (isset($cfg['db_prefix'])) {
			$conn->setPrefix($cfg['db_prefix']);
		}
		
		// Для MySQL выполняется установка кодировки в utf8.
		// Установка типа таблиц, используется для импорта.
		if ($cfg['db_lib'] !== 'PDO' and ($cfg['db_type'] == 'mysqli' or $cfg['db_type'] == 'mysql')) {
			$conn->exec('SET NAMES utf8');
			$conn->setDbType('mysql');
		} else {
			$conn->setDbType($cfg['db_type']);
		}

		return $conn;
	}
	
	/**
	 * Увеличение счетчика запросов на единицу.
	 */
	static public function incrementQueryCount()
	{
		self::$_query_count++;
	}
	
	/**
	 * Получение текущего значения счетчика запросов.
	 * 
	 * @return int
	 */
	static public function getQueryCount()
	{
		return self::$_query_count;
	}
	
	/**
	 * Поиск дублирующися запросов.
	 * 
	 * @return array
	 */
	static public function getQueryesDublicates()
	{
		$data = array();
		$tmp = array();
		foreach (self::$query_log as $num => $query) {
			$tmp[md5($query)][$num] = $query;
		}
		
		foreach ($tmp as $key => $value) {
			if (count($value) > 1) {
				$data[$key] = $value;
			}
		}
		return $data;
	}
	
	/**
	 * Получить сводную статистику по выполненным запросам.
	 * 
	 * @return array
	 */
	static public function getStat()
	{
		$slowest_query = '';
		$slowest_query_id = 'NULL';
		$total_time = 0;
		$max_time = 0;
		foreach (self::$query_log_profiler as $key => $value) {
			if ($max_time < $value) {
				$slowest_query		= self::$query_log[$key];
				$slowest_query_id	= $key;
				$max_time			= $value;
			}
			$total_time += $value;
		}
		$data = array(
			'total_time'		=> $total_time,
			'max_time'			=> $max_time,
			'slowest_query'	 	=> $slowest_query,
			'slowest_query_id'	=> $slowest_query_id,
			);
		return $data;
	}
}