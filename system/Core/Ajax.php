<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Обработка Ajax запросов.
 * 
 * @author	Artem Ryzhkov
 * @package	Kernel
 * @link	http://smart-core.org/
 * @license	http://opensource.org/licenses/gpl-2.0
 * 
 * @uses 	Node
 * @uses 	Response
 * 
 * @version 2011-07-09.0
 */
class Ajax extends Controller
{
	protected $front_end_action_mode = false;
	protected $front_end_action_node_id = false;

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
	 * NewFunction
	 *
	 * @param
	 * @return
	 */
	public function run($uri_path)
	{
		$uri_path_parts = explode('/', $uri_path);

		// Выключаем вывод данных по умолчанию, ajax методы, обязаны самостоятельно готовить выходные данные.
		$this->Response->setDirectData(null);
		
		if (is_numeric($uri_path_parts[0])) {
			// Проверка на существование ноды.
			$sql = "SELECT * 
				FROM {$this->DB->prefix()}engine_nodes
				WHERE site_id = '{$this->Env->site_id}'
				AND node_id = '{$uri_path_parts[0]}' ";
			$result = $this->DB->query($sql);
			if ($result->rowCount() == 1) {
				$Node = new Node();
				$Module = $Node->getModuleInstance($uri_path_parts[0]);
				$Module->ajax(str_replace($uri_path_parts[0] . '/', '', $uri_path));
			} else {
				return false;
			}
		}
		
	}

	/**
	 * NewFunction
	 *
	 * @param
	 * @return
	 */
	public function getFrontEndActionMode()
	{
		return $this->front_end_action_mode;
	}	

	public function getFrontEndActionNodeId()
	{
		return $this->front_end_action_node_id;
	}
	
}