<?php 
/**
 * The PHP Data Objects (PDO) Database extension.
 * 
 * @package		Kernel
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * @package 	Kernel
 * @subpackage	DB
 * 
 * @version 	2012-01-04.0
 */
class DB_PDO extends PDO
{
	/**
	 * Тип СУБД.
	 * @var string
	 */
	private $db_type;
	
	/**
	 * Префикс таблиц
	 * @var string
	 */
	private $prefix = '';
	
	/**
	 * Импорт SQL файла.
	 * 
	 * Файл разбивается на одиночные запросы с помощью метода explode(). Признаком окончания запроса является символы ";\n".
	 *
	 * @param string $file_path - путь к файлу, без расширения.
	 * @param array $params - параметры для замены.
	 * @return bool
	 */
	public function import($file_path, array $params = null)
	{
		$file = $file_path . '.' . $this->db_type;
		if (file_exists($file)) {
			$query = file_get_contents($file);
			
			foreach ($params as $key => $value) {
				$query = str_replace('{' . $key . '}', $value, $query);
			}

			$this->beginTransaction();
			
			foreach (explode(";\n", $query) as $value) {
				$q = trim($value);
				if (!empty($q)) {
					$this->exec($q);
					if ($this->errorCode() != 0000) {
						$this->rollBack();
						ob_start();
						ob_implicit_flush(false);
						echo "ErrorInfo: " . print_r(parent::errorInfo()) . "SQL: $query\nTrace: ";
						debug_print_backtrace();
						Log::put(ob_get_clean(), 'db/');
					}
				}
			}

			$this->commit();
			return true;
		} else {
			return false;
		}
	}
	
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
	 * Получить ID последней вставленной строки.
	 * 
	 * @return int
	 */
	public function lastInsertId($name = null)
	{
		return (int) parent::lastInsertId($name);
	}	

	/**
	 * Executes an SQL query, returning a result set as a PDO object
	 */
	public function query($query, $is_logging_errors = true)
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
		if ($is_logging_errors and !is_object($result)) {
			ob_start();
			ob_implicit_flush(false);
			echo "ErrorInfo: " . print_r(parent::errorInfo()) . "SQL: $query\nTrace: ";
			debug_print_backtrace(); // @todo Для PHP > 5.3.6+ покрутить параметры.
			Log::put(ob_get_clean(), 'db/');
			die('Database query error');
		}
		
		return $result;
	}
	
	/**
	 * Execute an SQL statement and return the number of affected rows
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
			debug_print_backtrace(); // @todo Для PHP > 5.3.6+ покрутить параметры.
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
	 * @param string $query     the SQL query
	 * @param mixed $params  
	 * @param int $fetchmode  the fetch mode to use
	 *
	 * @return array|object
	 * 
	 * @todo Обработку ошибок.
	 */
	public function getRow($query, $placeholders = array(), $fetchmode = DB::FETCHMODE_DEFAULT)
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
	 * @param string $query
	 * @param mixed $placeholders  
	 * @return object
	 */
	public function getRowObject($query, $placeholders = array())
	{
		return $this->getRow($query, $placeholders = array(), DB::FETCHMODE_OBJECT);
	}
}
