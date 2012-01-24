<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Системные настройки.
 * 
 * @version	2011-12-23.0
 */
class Settings extends Base
{
	private $_settings;

	/**
	 * Конструктор. Синглтон паттерн.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->_settings = array();
		
		$sql = "SELECT variable, default_value FROM {$this->DB->prefix()}engine_settings ";
		$result = $this->DB->query($sql);
		while($row = $result->fetchObject()) {
			$this->_settings[$row->variable] = $row->default_value;
		}
		
		$sql = "SELECT variable, value FROM {$this->DB->prefix()}engine_settings_values WHERE site_id = '" . $this->Env->site_id . "' ";
		$result = $this->DB->query($sql);
		while($row = $result->fetchObject()) {
			$this->_settings[$row->variable] = $row->value;
		}
		
		//$this->_settings['language_id'] = $this->_settings['default_language_id'];
		// @todo наврно надо будет сделать опцию настройки языка ИНТЕРФЕЙСА для пользователя
//		$this->_settings['user_interface_language_id'] = 1; 
	}
	
	/**
	 * Методы класса настроек
	 * 
	 * Надо предусмотреть возможность чтения параметров из конфигрурационного файла, 
	 * они будут читаться без подключения к БД,т.к. значения этих же параметров 
	 * необходимо для подключения к этой самой БД :)
	 * 
	 */
	public function getParam($name)
	{
		return $this->_settings[$name];
	}
	
	public function setParam($name, $value)
	{
		$this->_settings[$name] = $value;
	}
	
	/*
	public function getArray()
	{
		return $this->_settings;
	}
	*/
	
	public function setLanguageID($id)
	{
		$this->_settings['language_id'] = $id;
	}

	/**
	 * при вызове маинтенанса, не будет происходить подключение к БД.
	 * 
	 */
	public function getMaintenanceSettings()
	{
	}
	
	public function isMaintenance()
	{
	}

	
}
