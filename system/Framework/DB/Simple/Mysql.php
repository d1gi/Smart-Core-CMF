<?php 
/**
 * Адаптер mysql.
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
class DB_Simple_Mysql extends DB_Simple_Common
{
	/**
	 * Подключение к БД.
	 * 
	 * @param array $cfg
	 * @param bool $persistent
	 */
	public function connect(array $cfg, $persistent = false)
	{
		$this->connection = mysql_connect($cfg['db_host'] . ':' . $cfg['db_port'], $cfg['db_user'], $cfg['db_pass']) or die ('Not connected : ' . mysql_error());
		mysql_select_db($cfg['db_name'], $this->connection) or die ('Can\'t use '. $cfg['db_name'] .' : ' . mysql_error());
	}

	/**
	 * Выполнение простого запроса.
	 * 
	 * @param text $query
	 * @return object|bool
	 */
	protected function simpleQuery($query)
	{
		return mysql_query($query, $this->connection);	
	}	
	
	/**
	 * Выполнение простого запроса и возврат кол-во затронутых строк.
	 * 
	 * @param text $query
	 * @return int
	 */
	protected function simpleExec($query)
	{
		mysql_query($query, $this->connection); //or die("Invalid query: " . mysqli_errno() . ": " . mysqli_error() );
		return mysql_affected_rows($this->connection);
	}	
	
	/**
	* @private !!!
	* @access private
	* @return object
	*/
	public function _fetchObject($result, &$arr)
	{
		$arr = mysql_fetch_object($result);
	}

	/**
	* @private !!!
	* @access private
	* @return array
	*/
	public function _fetchRow($result, &$arr)
	{
		$arr = mysql_fetch_row($result);
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
		return mysql_num_rows($result);
	}	

	/**
	* Получить ИД последней добавленной записи.
	* 
	* @access private
	* 
	* @return int
	* 
	* @todo реализовать через LAST_INSERT_ID(), подробности тут: http://php.net/manual/ru/function.mysql-insert-id.php
	*/
	public function lastInsertId()
	{
		return (int) mysql_insert_id();
	}	

	/**
	* Экранирование строки, также добавляются одинарные кавычки в начале и в конце.
	* 
	* @param string $string
	* @return text
	*/
	public function quote($string)
	{
		return "'" . mysql_real_escape_string($string, $this->connection) . "'";
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
			$result = mysql_query($query, $this->connection);
			switch ($fetchmode) {
				case DB::FETCHMODE_DEFAULT:
				case DB::FETCHMODE_ASSOC:
					$row = mysql_fetch_assoc($result);
					break;
				case DB::FETCHMODE_OBJECT:
					$row = mysql_fetch_object($result);
					break;
				default;
					$row = null;
			}		
		}
		// Плейсхолдеры применяются.
		// @todo сделать поддержку Плейсхолдеров.
		else {
			die('DB_Simple_Mysql does not support placeholders yet, please use db_lib = PDO');
			// @see DB_Simple_Mysqli->getRow()
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