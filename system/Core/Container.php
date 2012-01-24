<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Контейнеры.
 * 
 * @author	Artem Ryzhkov
 * @package	Kernel
 * @license	http://opensource.org/licenses/gpl-2.0
 * 
 * @uses	EE
 * @uses	Kernel
 * 
 * @version 2011-07-13.0
 */
class Container extends Controller
{
	private $container_list = array();
	
	/**
	 * Конструктор
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Получить список контейнеров.
	 * 
	 * @param int $site_id
	 * @return array
	 */
	public function getList($site_id = false)
	{
		$data = array();
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}engine_containers
			WHERE site_id = '{$this->Env->site_id}'
			ORDER BY pos ASC";
		$result = $this->DB->query($sql);
		while($row = $result->fetchObject()) {
			$data[$row->container_id] = array(
				'name'		=> $row->name,
				'descr'		=> $row->descr,
				'pos'		=> $row->pos,
				'site_id'	=> $row->site_id,
				'create_datetime'	=> $row->create_datetime,
				'owner_id'	=> $row->owner_id,
				);
		}
		return $data;
	}
	
	/**
	 * Получить массив для применения в Zend_Form multiOptions
	 * 
	 * @return array
	 */
	public function getHtmlSelectOptionsArray()
	{
		if (count($this->container_list) == 0) {
			$this->container_list = $this->getList();
		}
		
		$multi_options = array();
		foreach ($this->container_list as $key => $value) {
			$multi_options[$key] = $value['descr'] . ' (' . $value['name'] . ')';
		}		
		
		return $multi_options;
	}
	
}