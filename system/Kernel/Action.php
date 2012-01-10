<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Обработка Action запросов.
 * 
 * @author	Artem Ryzhkov
 * @package	Kernel
 * @link	http://smart-core.org/
 * @license	http://opensource.org/licenses/gpl-2.0
 * 
 * @uses 	EE
 * @uses 	Node
 * @uses 	Output
 * 
 * @version 2011-11-03.0
 */
class Action extends Base
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
	 * Обработка запроса действия над нодой.
	 * 
	 * @uses Module_*
	 * 
	 * @param string $node_id
	 * @param string $uri_path
	 * @return void
	 */
	public function run($uri_path)
	{
		$uri_path_parts = explode('/', $uri_path);
		
		$node_id = $uri_path_parts[0];
		
		// Если первая часть запроса является числом, то считается, что это действие над нодой.
		if (is_numeric($node_id)) {
			$uri_path = substr($uri_path, strlen($node_id));
			// Проверка на наличие ноды в текущей папке.
			$sql = "SELECT
					n.node_id,
					n.folder_id,
					n.module_id,
					n.node_action_mode,
					n.database_id,
					n.params,
					n.permissions,
					c.name AS container_name
				FROM {$this->DB->prefix()}engine_nodes AS n
				LEFT JOIN {$this->DB->prefix()}engine_containers AS c USING (container_id)
				WHERE n.node_id = '$node_id'
				AND n.folder_id = '{$this->Env->current_folder_id}'
				AND n.is_active = 1
				AND n.site_id = '{$this->Env->site_id}'
				AND c.site_id = '{$this->Env->site_id}' ";
			$result = $this->DB->query($sql);
			
			// Ноды НЕТ прямо в текущей папке, по этому выполняется поиск в наследованных контейнерах. 
			if ($result->rowCount() === 0) { 
				$node_pass = false;
				$sql = "SELECT
						n.node_id,
						n.folder_id,
						n.module_id,
						n.node_action_mode,
						n.database_id,
						n.params,
						n.permissions,
						c.name AS container_name
					FROM {$this->DB->prefix()}engine_nodes AS n
					LEFT JOIN {$this->DB->prefix()}engine_containers_inherit AS ci USING (container_id)
					LEFT JOIN {$this->DB->prefix()}engine_containers AS c USING (container_id)
					WHERE n.node_id = '$node_id'
					AND n.is_active = 1
					AND n.site_id = '{$this->Env->site_id}'
					AND c.site_id = '{$this->Env->site_id}'
					AND ci.site_id = '{$this->Env->site_id}'
					GROUP BY node_id ";
				$result = $this->DB->query($sql);
				
				if ($result->rowCount() === 1) {
					$row = $result->fetchObject();
					$node_pass = true;
				}
			} elseif ($result->rowCount() === 1) { 
				// Нода ЕСТЬ в текущей папке.
				$row = $result->fetchObject();
				$node_pass = true;
			}
			  
			// Такая нода в этой папке есть!
			if ($node_pass) { 
				$Node = new Node();
				$Module = $Node->getModuleInstance($node_id, true);
				if ($this->Permissions->isAllowed('node', 'write', $row->permissions)) {
					$Module->setWritable(true);
				}

				// Удаляется первый слеш и запускается действие.
				$Module->nodeAction(substr($uri_path, 1));
				
				// @todo сейчас используется контейнер content для popup режима. возможно надо другой заюзать, например некий 'default'.
				if ($row->node_action_mode === 'popup') {
					// @todo можно будет сделать смену тем для админки.
					$this->EE->template['dir_theme'] = DIR_KERNEL . 'Admin/';
					$this->EE->template['theme_name'] = ''; 
					$this->EE->template['layout'] = 'blank';
					$this->EE->addHeadScript('backend.js', HTTP_SYS_RESOURCES . 'admin/backend/backend.js');
					$this->EE->addDocumentReady('fieldsetsToTabs($j);');
					
					// @todo !!! сделать нормально ;) popup_iframe.css не нужен для built-in режима.
					$this->EE->addHeadStyle('system_admin.css', HTTP_SYS_RESOURCES . 'admin/backend/system_admin.css');
					$this->EE->addHeadStyle('popup_iframe.css', HTTP_SYS_RESOURCES . 'styles/popup_iframe.css');
					
					$container = 'content';
				} else {
					$container = $row->container_name;
				}
				
				$this->EE->data[$container][$node_id] = array(
					'node_action_mode'	=> $row->node_action_mode,
					'tpl'				=> $Module->getTpl(),
					'tpl_path' 			=> $Module->getTplPath(),
					'data'				=> $Module->getOutputData(),
					);
				
				$this->front_end_action_mode = $row->node_action_mode;
				
				// Запоминается ИД ноды, на которую применён action, дальше она собираться больше не будет.
				$this->front_end_action_node_id = $node_id;
				unset($Module);
			}
		}
		return true;
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