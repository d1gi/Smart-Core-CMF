<?php
/**
 * Управление Компонентами.
 * 
 * @category	System
 * @package		Kernel
 * 
 * @uses 		DB
 * 
 * @version		2012-01-25.0
 */
class Admin_Component extends Controller
{
	/**
	 * Constructor.
	 *
	 * @param void
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * run
	 * 
	 * @param string $uri_path
	 * @return array
	 */
	public function run($uri_path)
	{
		if (empty($uri_path)) {
			$this->View->components = $this->getСomponentsList();
			$this->View->setTpl('component');
		} else {
			$uri_path_parts = explode('/', $uri_path);
			$this->Breadcrumbs->add($uri_path_parts[0] . '/', $uri_path_parts[0]);
			$component_class_name = 'Component_' . $uri_path_parts[0] . '_Admin';
			$Component = new $component_class_name(false);
			$Component->admin(substr($uri_path, strlen($uri_path_parts[0]) + 1));
			$this->View = $Component->View;
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
}