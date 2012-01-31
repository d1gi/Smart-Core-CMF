<?php
/**
 * Управление модулями.
 * 
 * @category	System
 * @package		Kernel
 * @subpackage	Module
 * 
 * @uses 		Breadcrumbs
 * @uses 		DB
 * @uses 		View
 * 
 * @version		2012-01-24.0
 */
class Admin_Module extends Controller
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * run
	 * 
	 * @param string $uri_path
	 */
	public function run($uri_path)
	{
		if (empty($uri_path)) {
			$this->View->setTpl('module');
			$this->View->modules = $this->getModulesList();
		} else {
			$uri_path_parts = explode('/', $uri_path);
			if ($this->isModuleExist($uri_path_parts[0])) {
				
				$this->Breadcrumbs->add($uri_path_parts[0] . '/', $uri_path_parts[0]);
				
				$module_class_name = 'Module_' . $uri_path_parts[0] . '_Admin';
				$Module	= new $module_class_name(false);
				$Module->admin(substr($uri_path, strlen($uri_path_parts[0]) + 1));
				$this->View = $Module->View;
			}
		}
	}
	
	/**
	 * Получение списка модулей.
	 *
	 * @return array
	 */
	public function getModulesList()
	{
		$modules = array();
		$sql = "SELECT * FROM {$this->DB->prefix()}engine_modules ORDER BY module_id ASC ";
		$result = $this->DB->query($sql);

		$cnt = 0;
		while ($row = $result->fetchObject()) {
			$is_managed = false;

			$module_class_name = 'Module_' . $row->module_id . '_Admin';
			$Module = new $module_class_name(false);

			if ($Module->admin(false) !== false) {
				$is_managed = true;
			}

			$modules[$row->module_id] = array(
				'template'	 => $row->template,
				'descr'		 => $row->descr,
				'is_managed' => $is_managed,
				);
		}

		return $modules;
	}
	
	/**
	 * Проверить, существует ли модуль.
	 *
	 * @param string $module_id - имя модуля.
	 * @return bool
	 */
	public function isModuleExist($module_id)
	{
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}engine_modules
			WHERE module_id = {$this->DB->quote($module_id)}
			ORDER BY module_id ASC ";
		$result = $this->DB->query($sql);
		if ($result->rowCount() == 1 and class_exists('Module_' . $module_id)) {
			return true;
		} else {
			return false;
		}
	}
}