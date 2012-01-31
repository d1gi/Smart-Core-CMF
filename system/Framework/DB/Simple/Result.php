<?php
/**
 * Интерфейс над результатами запроса, например $result->rowCount()
 */
class DB_Simple_Result
{
	public $dbh;
	public $result;
	
	/**
	 * Конструктор
	 */
	public function __construct(&$dbh, $result, $options = array())
	{
		$this->dbh = &$dbh;
		$this->result = $result;
	}
	
	/**
	 * Возвращает массив, содержашей данные обработанного ряда, или FALSE, если рядов больше нет.
	 */
	public function &fetchRow()
	{
		$this->dbh->_fetchRow($this->result, $arr);
		return $arr;
	}
	
	/**
	 * Возвращает объект со свойствами, соответствующий извлечённому ряду, либо FALSE , если рядов больше нет.
	 */
	public function &fetchObject()
	{
		$this->dbh->_fetchObject($this->result, $arr);
		return $arr;
	}
	
	/**
	 * Возвращает число строк, затронутых при выполнении последней инструкции.
	 */
	public function rowCount()
	{
		return $this->dbh->_rowCount($this->result);
	}
}