<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Управление Компонентами.
 * 
 * @category	System
 * @package		Kernel
 * 
 * @uses 		DB
 * @uses 		EE
 * 
 * @version		2011-12-03.0
 */
class Admin_Component extends Base
{
	protected $component_name;
	
	/**
	 * Constructor.
	 *
	 * @param void
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setTpl('component');
		$this->setTplPath(DIR_KERNEL . 'Admin/');
		$this->component_name = 'Admin';
	}

	/**
	 * Action...
	 * 
	 * @param string $uri_path
	 * @return array
	 */
	public function action($uri_path)
	{
		if (empty($uri_path)) {
			$this->output_data['components'] = $this->getСomponentsList();
		} else {
			$uri_path_parts = explode('/', $uri_path);
			//if ($this->isModuleExist($uri_path_parts[0])) {
			$this->EE->addBreadCrumb($this->EE->breadcrumbs[count($this->EE->breadcrumbs) - 1]['uri'] . $uri_path_parts[0] . '/', $uri_path_parts[0]);
			$component_class_name	= 'Component_' . $uri_path_parts[0] . '_Admin';
			$Component				= new $component_class_name(false);
			$this->output_data		= $Component->admin(substr($uri_path, strlen($uri_path_parts[0]) + 1));
			$this->component_name	= $uri_path_parts[0];
			$this->setTpl($Component->getTpl());
			$this->setTplPath(DIR_COMPONENTS . $uri_path_parts[0] . '/');
			//}
		}
	}
	
	/**
	 * Получение списка модулей.
	 *
	 * @param void
	 * @return array
	 */
	public function getСomponentsList()
	{
		$components = array();
		$sql = "SELECT * FROM {$this->DB->prefix()}engine_components ORDER BY component_id ASC ";
		$result = $this->DB->query($sql);

		$cnt = 0;
		while ($row = $result->fetchObject()) {
			/*
			$is_managed = false;

			$component_class_name = 'Component_' . $row->component_id . '_Admin';
			$Component_id = new $component_class_name(false);

			if ($Component->admin(false) !== false) {
				$is_managed = true;
			}
			*/
			$is_managed = true; // @todo пока так ;)
			
			$components[$row->component_id] = array(
				'title'		 => $row->title,
				'descr'		 => $row->descr,
				'is_managed' => $is_managed,
				);
		}

		return $components;
	}
	
	/**
	 * Получить имя компонента.
	 *
	 * @param void
	 * @return string
	 */
	public function getModuleName()
	{
		return $this->component_name;
	}
	
}