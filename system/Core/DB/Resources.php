<?php 
/**
 * Хранилище доступных подключеный к БД.
 * 
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @uses DB
 * 
 * @version		2012-01-17.0
 */
class DB_Resources extends Singleton
{
	/**
	 * Массив со всеми подключениями.
	 */
	private $database_resources = array();

	/**
	 * Собираем массив со всеми возможными подключениями.
	 */
	protected function __construct()
	{
		$DB = Registry::get('DB');
		$sql = "SELECT * FROM {$DB->prefix()}engine_database_resources";
		$result = $DB->query($sql);
		while ($row = $result->fetchObject()) {
			$this->database_resources[$row->database_id] = array(
				'db_lib'	 => $row->lib,
				'db_type'	 => $row->driver,
				'db_host'	 => $row->db_host,
				'db_port'	 => $row->db_port,
				'db_user'	 => $row->db_user,
				'db_pass'	 => $row->db_password,
				'db_name'	 => $row->db_name,
				'db_persist' => $row->db_persist,
				'name'		 => $row->name,
				'title'		 => $row->title,
				);
		}
	}	

	/**
	 * Возвращает массив с данными указанного подключения.
	 */
	public function getConnectionData($database_id)
	{
		// База ядра имеет id = 0, данных не возвращаем.
		return $database_id == 0 ? 0 : $this->database_resources[$database_id];
	}
	
	/**
	 * Получить полный список подключений к БД.
	 *
	 * @return array
	 */
	public function getList()
	{
		return $this->database_resources;
	}
	
	/**
	 * Получить список подключений для применения в формах.
	 *
	 * @return array
	 */
	public function getListOptions()
	{
		$databases = array(
			'0' => '0 - Системная база данных',
			);
		foreach ($this->database_resources as $id => $value) {
			$databases[$id] = $id . ' - ' . $value['title'];
		}
		return $databases;
	}
}