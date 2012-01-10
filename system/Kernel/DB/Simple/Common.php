<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Родительский класс абстракной библиотеки для работы с БД.
 * 
 * @version 2011-11-22.0
 */
abstract class DB_Simple_Common
{
	/**
	 * Ссылка на подключение к БД.
	 * @var resource
	 */
	protected $connection;

	/**
	 * Тип СУБД.
	 * @var string
	 */
	protected $db_type;
	
	/**
	 * Префикс таблиц
	 * @var string
	 */
	protected $prefix = '';

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
	 * Выполнить SQL запрос и вернуть результирующую таблицу в виде объекта.
	 * 
	 * @return object DB_Simple_Result
	 */
	public function query($query, $is_logging_errors = true)
	{
		$cnt = DB::getQueryCount();
		if (DEBUG_DB_QUERY) {
			DB::$query_log[$cnt] = $query;
			$start_time = microtime(true);
		}
		
		DB::incrementQueryCount();
		
		$result = $this->simpleQuery($query);

		if (DEBUG_DB_QUERY) {
			DB::$query_log_profiler[$cnt] = microtime(true) - $start_time;
		}

		$tmp = clone new DB_Simple_Result($this, $result);
		return $tmp;
	}
	
	/**
	 * Выполнить SQL запрос и вернуть кол-во затронутых строк.
	 * 
	 * @return int
	 */
	public function exec($query)
	{
		$cnt = DB::getQueryCount();
		if (DEBUG_DB_QUERY) {
			DB::$query_log[$cnt] = $query;
			$start_time = microtime(true);
		}

		DB::incrementQueryCount();
		
		$result = $this->simpleExec($query);
		
		if (DEBUG_DB_QUERY) {
			DB::$query_log_profiler[$cnt] = microtime(true) - $start_time;
		}
		
		return $result;
	}
	
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
			
			$this->query('SET AUTOCOMMIT=0');
			$this->query('START TRANSACTION');
			
			foreach (explode(";\n", $query) as $value) {
				$q = trim($value);
				if (!empty($q)) {
					$this->simpleQuery($query);
				}
			}
			
			if ($this->errorCode() != 0000) {
				$this->query('ROLLBACK');
				$_SESSION['messages'][] = 'MySQL Error: ' . $this->errorCode();
			}

			$this->query('COMMIT');
			return true;
		} else {
			return false;
		}
	}
}