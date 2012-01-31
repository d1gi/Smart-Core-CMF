<?php 
/**
 * Адаптер mysqli.
 * 
 * @package		Kernel
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * @package 	Kernel
 * @subpackage	DB
 * 
 * @version 2011-12-27.0
 */
class DB_Simple_Mysqli extends DB_Simple_Common
{
	/**
	 * Подключение к БД.
	 * 
	 * @param array $cfg
	 * @param bool $persistent
	 */
	public function connect($cfg, $persistent = false)
	{
		$this->connection = mysqli_connect($cfg['db_host'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_name'], $cfg['db_port']) or die ('Not connected : ' . mysqli_error());
	}
	
	/**
	 * Выполнение простого запроса.
	 * 
	 * @param text $query
	 * @return object|bool
	 */
	protected function simpleQuery($query)
	{
		return mysqli_query($this->connection, $query); //or die("Invalid query: " . mysqli_errno() . ": " . mysqli_error() );	
	}	
	
	/**
	 * Выполнение простого запроса и возврат кол-во затронутых строк.
	 * 
	 * @param text $query
	 * @return int
	 */
	protected function simpleExec($query)
	{
		mysqli_query($this->connection, $query); //or die("Invalid query: " . mysqli_errno() . ": " . mysqli_error() );
		return mysqli_affected_rows($this->connection);
	}	
	
	/**
	* @private !!!
	* 
	* @access private
	* @return object
	*/
	public function _fetchObject($result, &$arr)
	{
		$arr = mysqli_fetch_object($result);
	}

	/**
	* @private !!!
	* 
	* @access private
	* @return array
	*/
	public function _fetchRow($result, &$arr)
	{
		$arr = mysqli_fetch_row($result);
		
	}

	/**
	* Возвращает число строк, затронутых при выполнении последней инструкции.
	* 
	* @private !!!
	* @access private
	* 
	* @param mixed $result
	* @return int
	*/
	public function _rowCount($result)
	{
		return mysqli_num_rows($result);
	}	

	/**
	* Получить ИД последней добавленной записи.
	* 
	* @access private
	* 
	* @return int
	*/
	public function lastInsertId()
	{
		return (int) mysqli_insert_id();
	}	
	
	/**
	* Экранирование строки, также добавляются одинарные кавычки в начале и в конце.
	* 
	* @param string $string
	* @return text
	*/
	public function quote($string)
	{
		return "'" . mysqli_real_escape_string($this->connection, $string) . "'";
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
		if (DEBUG_DB_QUERY) {
			DB::$query_log[DB::getQueryCount()] = $query;
		}
		
		DB::incrementQueryCount();

		// Плейсхолдеров нет, по этому выполняются нативные запросы.
		if (empty($placeholders)) {
			$result = mysqli_query($this->connection, $query);
			switch ($fetchmode) {
				case DB::FETCHMODE_DEFAULT:
				case DB::FETCHMODE_ASSOC:
					$row = mysqli_fetch_assoc($result);
					break;
				case DB::FETCHMODE_OBJECT:
					$row = mysqli_fetch_object($result);
					break;
				default;
					$row = null;
			}		
		}
		// Плейсхолдеры применяются.
		// @todo сделать поддержку Плейсхолдеров.
		else {
			die('DB_Simple_Mysqli does not support placeholders yet, please use db_lib = PDO');
			/*
			foreach ($placeholders as $key => $value) {
				$query = str_replace($key, '?', $query);
			}

			$stmt = $this->connection->stmt_init();
			$stmt->prepare($query);
			
			foreach ($placeholders as $key => $value) {
				$stmt->bind_param($key, $value);
			}

			$stmt->execute(); // $params

			switch ($fetchmode) {
				case DB::FETCHMODE_DEFAULT:
				case DB::FETCHMODE_ASSOC:
					//$row = $stmt->fetch(PDO::FETCH_ASSOC);
					break;
				case DB::FETCHMODE_OBJECT:
					$stmt->bind_result($row);
					$stmt->fetch();
					break;
				default;
					$row = null;
			}
			$stmt->close();
			*/
		}
		
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