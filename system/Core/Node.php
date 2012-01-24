<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс по работе с Нодами.
 * 
 * @author		Artem Ryzhkov
 * @category	System
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @uses		Kernel
 * 
 * @version		2012-01-05.0
 */
class Node extends Controller
{
	/**
	 * Node ID.
	 * @var int
	 */
	protected $node_id = false;
	
	/**
	 * Объект модуля.
	 * @var object
	 */
	protected $Module;
	
	/**
	 * Constructor.
	 *
	 * @param
	 * @return
	 */
	public function __construct($node_id = false)
	{
		parent::__construct();
		if ($node_id) {
			$this->activate($node_id);
		}
	}
	
	/**
	 * Активировать ноду.
	 *
	 * @param int $node_id
	 * 
	 * @todo может быть и не нужно...
	 */
	public function activate($node_id)
	{
		$this->node_id = $node_id;
	}
	
	/**
	 * Получить свойва ноды.
	 *
	 * @param int $node_id
	 * @param string $property - получить конкретное свойство, если не указано, возвращаются все свойства.
	 * @return array|string
	 */
	public function getProperties($node_id, $property = false)
	{
		$sql = "SELECT *
			FROM {$this->DB->prefix()}engine_nodes
			WHERE node_id = '$node_id'
			AND site_id = '{$this->Env->site_id}' ";
		$result = $this->DB->query($sql);
		if ($result->rowCount() == 1) {
			$row = $result->fetchObject();
			
			if ($property === false) {
				$properties = array(
					'is_active'		=> $row->is_active,
					'folder_id'		=> $row->folder_id,
					'module_id'		=> $row->module_id,
					'container_id'	=> $row->container_id,
					'route_params'	=> false,
					'cache_params'	=> $row->cache_params,
					'params'		=> $row->params,
					'permissions'	=> $row->permissions,
					'plugins'		=> $row->plugins,
					'database_id'	=> $row->database_id,
					'descr'			=> $row->descr,
					);
				return $properties;
			} else {
				return $row->$property;
			}
		
		} else {
			return false;
		}
	}
	
	/**
	 * Получить объект модуля.
	 *
	 * @param int $node_id
	 * @param bool $is_admin - вернуть объект с административными методами.
	 * @return object
	 */
	public function getModuleInstance($node_id = false, $is_admin = false)
	{
		/**
		  Array
			(
				[folder_id] => 1
				[module_id] => Texter
				[container_id] => 3
				[params] => a:1:{s:12:"text_item_id";s:1:"1";}
				[permissions] => 
				[parser_data] => 
				[database_id] => 0
				[node_action_mode] => popup
				[session] => 0
			)
		 */
		$properties = Kernel::getNodeData($node_id);
		
		if ($properties === null) {
			$properties = $this->getProperties($node_id);
		}
		
		if ($properties === null) {
			return null;
		}

		$class = 'Module_' . $properties['module_id'];
		if ($is_admin) {
			$class .= '_Admin';
		}

		return new $class($node_id);
	}
	
	/**
	 * Создание новой ноды.
	 *
	 * @param array $pd
	 * @return bool
	 */
	public function create($pd)
	{
		if (is_numeric($pd['container_id'])) {
			$container_id = $pd['container_id'];
		} else {
			return false;
		}

		// Вычисление максимальной позиции, чтобы поместить новую ноду в конец внутри контейнера.
		/*
		$sql = "SELECT max(pos) as max_pos 
			FROM {$this->DB->prefix()}engine_nodes
			WHERE container_id = '$pd[container_id]'
			AND folder_id = '$pd[folder_id]'
			AND site_id = '{$this->Env->site_id}' ";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		$max_pos = $row->max_pos + 1;
		*/
		$pos			= is_numeric($pd['pos']) ? $pd['pos'] : 0;
		$database_id	= (isset($pd['database_id']) and is_numeric($pd['database_id'])) ? $pd['database_id'] : 0;
		$is_active		= (isset($pd['is_active']) and is_numeric($pd['is_active'])) ? $pd['is_active'] : 1;
		$is_cached		= is_numeric($pd['is_cached']) ? $pd['is_cached'] : 1;
		$folder_id		= is_numeric($pd['folder_id']) ? $pd['folder_id'] : 1;
		$permissions	= strlen(trim($pd['permissions'])) == 0 ? 'NULL' : $this->DB->quote(trim($pd['permissions']));
		
		$descr = $this->DB->quote(trim($pd['descr']));
		$sql = "
			INSERT INTO {$this->DB->prefix()}engine_nodes
				(folder_id, site_id, descr, container_id, module_id, database_id, params, is_active, is_cached, pos, permissions, create_datetime, owner_id)
			VALUES
				('$folder_id', '{$this->Env->site_id}', $descr, '$container_id', '$pd[module_id]', '$database_id', NULL, '$is_active', '$is_cached', '$pos', $permissions, NOW(), '{$this->Env->user_id}') ";
		$this->DB->query($sql);
		$node_id = $this->DB->lastInsertId();
		
		$Node = new Node();
		$Module = $Node->getModuleInstance($node_id, true);
		$params = $Module->createNode();
		
		if ($params != 'NULL') {
			$params = "'" . serialize($params) . "'";
		}
		
		$sql = "
			UPDATE {$this->DB->prefix()}engine_nodes SET
				params = $params
			WHERE node_id = '$node_id'
			AND site_id = '{$this->Env->site_id}' ";
		$this->DB->exec($sql);
		$this->Cache->updateFolder($folder_id);
		return true;
	}
	
	/**
	 * Получить список всех нод.
	 *
	 * @param
	 * @return array
	 * 
	 * @todo постраничность.
	 */
	public function getList($items_per_page = false, $page_num = 1)
	{
		return $this->getListInFolder();
	}
	
	/**
	 * Получить список нод в папке.
	 * 
	 * @param int $folder_id - если false, то возвращается список всех нод.
	 * @return array
	 */
	public function getListInFolder($folder_id = false)
	{
		$sql_folder = $folder_id === false ? '' : " AND folder_id = '$folder_id' ";
		
		$nodes = array();
		$sql = "SELECT
				n.node_id,
				n.container_id,
				n.folder_id,
				n.pos,
				n.module_id,
				n.params,
				n.plugins,
				n.is_cached,
				n.is_active,
				n.database_id,
				n.descr,
				c.name AS container_name,
				c.descr AS container_descr
			FROM {$this->DB->prefix()}engine_nodes AS n
			LEFT JOIN {$this->DB->prefix()}engine_containers AS c	USING (container_id)
			WHERE n.site_id = '{$this->Env->site_id}'
			AND c.site_id = '{$this->Env->site_id}'
			$sql_folder
			ORDER BY n.pos ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$nodes[$row->node_id] = array(
				'descr'			=> $row->descr,
				'is_active'		=> $row->is_active,
				'folder_id'		=> $row->folder_id,
				'pos'			=> $row->pos,
				'module_id'		=> $row->module_id,
				'database_id'	=> $row->database_id,
				'params'		=> $row->params,
				'plugins'		=> $row->plugins,
				'container_name' => $row->container_name,
				'container_descr' => $row->container_descr,
			);
		}
		return $nodes;
	}
	
	/**
	 * Получить список всех нод заданного модуля.
	 *
	 * @param string $module
	 * @return array
	 */
	public function getListByModule($module)
	{
		$data = array();
		$sql = "SELECT node_id 
			FROM {$this->DB->prefix()}engine_nodes
			WHERE site_id = '{$this->Env->site_id}'
			AND module_id = {$this->DB->quote($module)} ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$data[$row->node_id] = $this->getProperties($row->node_id);
		}
		return $data;
	}
 
	/**
	 * Обновление параметров ноды.
	 * 
	 * @param int $node_id
	 * @param array $pd
	 * @return bool
	 */
	public function update($node_id, $pd)
	{
		if (is_numeric($pd['container_id'])) {
			$container_id = $pd['container_id'];
		} else {
			return false;
		}
		
		$pos			= is_numeric($pd['pos']) ? $pd['pos'] : 0;
		$database_id	= (isset($pd['database_id']) and is_numeric($pd['database_id'])) ? $pd['database_id'] : 0;
		$is_active		= (isset($pd['is_active']) and is_numeric($pd['is_active'])) ? $pd['is_active'] : 1;
		$is_cached		= is_numeric($pd['is_cached']) ? $pd['is_cached'] : 1;
		$folder_id		= is_numeric($pd['folder_id']) ? $pd['folder_id'] : 1;
		$permissions	= strlen(trim($pd['permissions'])) == 0 ? 'NULL' : $this->DB->quote(trim($pd['permissions']));
		$params			= (!isset($pd['params']) or count($pd['params']) == 0) ? 'params = NULL' : "params = " . $this->DB->quote(serialize($pd['params']));
		$plugins		= strlen(trim($pd['plugins'])) == 0 ? 'NULL' : $this->DB->quote(trim($pd['plugins']));
		$cache_params_yaml	= (isset($pd['cache_params_yaml']) and !empty($pd['cache_params_yaml'])) ? 'cache_params_yaml = ' . $this->DB->quote($pd['cache_params_yaml']) : 'cache_params_yaml = NULL';
		$cache_params	= $cache_params_yaml == 'cache_params_yaml = NULL' ? 'cache_params = NULL' : 'cache_params = ' . $this->DB->quote(serialize(Zend_Config_Yaml::decode($pd['cache_params_yaml'])));
		$descr = $this->DB->quote(trim($pd['descr']));
		
		$sql = "
			UPDATE {$this->DB->prefix()}engine_nodes SET
				descr = $descr,
				folder_id = '$folder_id',
				pos = '$pos',
				database_id = '$database_id',
				container_id = '$container_id',
				is_active = '$is_active',
				is_cached = '$is_cached',
				permissions = $permissions,
				plugins = $plugins,
				$params,
				$cache_params,
				$cache_params_yaml
			WHERE
				node_id = '$node_id'
			AND site_id = '{$this->Env->site_id}' ";
		$this->DB->exec($sql);
		$this->Cache->updateNode($node_id);
		return true;
	}
	
	/**
	 * Хуки.
	 *
	 * @param string $method - имя вызываемого метода.
	 * @param array $args - массив с аргументами.
	 * @return mixed
	 */
	public function hook($method, array $args = null)
	{
		$Module = $this->getModuleInstance($this->node_id);
		return is_object($Module) ? $Module->hook($method, $args) : null;
	}
}