<?php 
/**
 * Zend_Db_Adapter_Pdo_Mysql
 * 
 * @package		Kernel
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * @package 	Kernel
 * @subpackage	DB
 * 
 * @version 	2011-12-27.0
 */
class DB_Zend extends Zend_Db_Adapter_Pdo_Mysql
{
	/**
	 * Тип СУБД.
	 */
	private $db_type;
	
	/**
	 * Префикс таблиц
	 * @var string
	 */
	private $prefix = '';

	/**
	 * Установить тип СУБД.
	 *
	 * @param string $db_type
	 */
	public function setDbType($db_type)
	{
		$this->db_type = $db_type;
	}
	
	/**
	 * Установить префикс таблиц.
	 *
	 * @param string $prefix
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}
	
	/**
	 * Получить значение префикса таблиц.
	 *
	 * @return string
	 */
	public function prefix()
	{
		return $this->prefix;
	}
		
	/**
	 * Executes an SQL query, returning a result set PDO object.
	 */
	public function query($query, $bind = array())
	{
		$cnt = DB::getQueryCount();
		if (DEBUG_DB_QUERY) {
			DB::$query_log[$cnt] = $query;
			$start_time = microtime(true);
		}

		DB::incrementQueryCount();
		
		$result = parent::query($query);

		if (DEBUG_DB_QUERY) {
			DB::$query_log_profiler[$cnt] = microtime(true) - $start_time;
		}

		// Ошибка выполнения запроса.
		if (!is_object($result)) {
			ob_start();
			ob_implicit_flush(false);
			echo "ErrorInfo: " . print_r(parent::errorInfo()) . "SQL: $query\nTrace: ";
			debug_print_backtrace(); // @todo Для PHP > 5.3.6 покрутить параметры.
			Log::put(ob_get_clean(), 'db/');
			die('Database query error');
		}
		
		return $result;
	}
	
	/**
	 * Execute an SQL query and return the number of affected rows
	 */
	public function exec($query)
	{
		$cnt = DB::getQueryCount();
		if (DEBUG_DB_QUERY) {
			DB::$query_log[$cnt] = $query;
			$start_time = microtime(true);
		}
		
		DB::incrementQueryCount();
		
		$result = parent::exec($query);
		
		if (DEBUG_DB_QUERY) {
			DB::$query_log_profiler[$cnt] = microtime(true) - $start_time;
		}

		// Ошибка выполнения запроса.
		if (!is_numeric($result)) {
			ob_start();
			ob_implicit_flush(false);
			echo "ErrorInfo: " . print_r(parent::errorInfo()) . "SQL: $query\nTrace: ";
			debug_print_backtrace(); // @todo Для PHP > 5.3.6 покрутить параметры.
			Log::put(ob_get_clean(), 'db/');
			die('Database exec error');
		}
		
		return $result;
	}
	
	/**
	 * Извлечение записи в виде массива или объекта.
	 *
	 * Пока извлекается только первая запись из результирующей таблицы.
	 * 
	 * @see DB_PDO->getRow()
	 */
	public function &getRow($query, $placeholders = array(), $fetchmode = DB::FETCHMODE_DEFAULT)
	{
		$cnt = DB::getQueryCount();
		if (DEBUG_DB_QUERY) {
			DB::$query_log[$cnt] = $query;
			$start_time = microtime(true);
		}
		
		DB::incrementQueryCount();

		$stmt = parent::prepare($query);
		$stmt->execute($placeholders);
		
		if (DEBUG_DB_QUERY) {
			DB::$query_log_profiler[$cnt] = microtime(true) - $start_time;
		}
		
		switch ($fetchmode) {
			case DB::FETCHMODE_DEFAULT:
			case DB::FETCHMODE_ASSOC:
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				break;
			case DB::FETCHMODE_OBJECT:
				$row = $stmt->fetch(PDO::FETCH_OBJ);
				break;
			default;
				$row = null;
		}
		$stmt->closeCursor();
		return $row;
	}
	
	/**
	 * Алиас для метода getRow() в режиме DB::FETCHMODE_OBJECT.
	 *
	 * @see DB_PDO->getRow()
	 */
	public function &getRowObject($query, $placeholders = array())
	{
		return $this->getRow($query, $placeholders = array(), DB::FETCHMODE_OBJECT);
	}
}