<?php
/**
 * Обработка Action запросов.
 * 
 * @author	Artem Ryzhkov
 * @package	Kernel
 * @link	http://smart-core.org/
 * @license	http://opensource.org/licenses/gpl-2.0
 * 
 * @uses 	Node
 * @uses 	Permissions
 * @uses 	ScriptsLib
 * @uses 	View
 * 
 * @version 2012-02-01
 */
class Action extends Controller
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

		View::prependPath(DIR_SYSTEM . 'Core/Admin/views/'); // @todo 
		$this->View = new Html();
		$this->View->setTpl('main');
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
	public function run($path)
	{
		if ($this->Permissions->isRoot() or $this->Permissions->isAdmin()) {
			// @todo убрать проверку прав в другое место.
		} else {
			return false;
		}
		
		
		$uri_path = $path['action_path'];
		$uri_path_parts = explode('/', $uri_path);
		
		$node_id = $uri_path_parts[0];
		
		// Если первая часть запроса является числом, то считается, что это действие над нодой.
		if (is_numeric($node_id)) {
			$uri_path = substr($uri_path, strlen($node_id));
			// Проверка на наличие ноды в текущей папке.
			$sql = "SELECT n.node_id, n.folder_id, n.module_id, n.node_action_mode,
					n.database_id, n.params, n.permissions, b.name AS block_name
				FROM {$this->DB->prefix()}engine_nodes AS n
				LEFT JOIN {$this->DB->prefix()}engine_blocks AS b USING (block_id)
				WHERE n.node_id = '$node_id'
				AND n.folder_id = '{$this->Env->current_folder_id}'
				AND n.is_active = 1
				AND n.site_id = '{$this->Env->site_id}'
				AND b.site_id = '{$this->Env->site_id}' ";
			$result = $this->DB->query($sql);
			
			// Ноды НЕТ прямо в текущей папке, по этому выполняется поиск в унаследованных блоках.
			if ($result->rowCount() === 0) { 
				$node_pass = false;
				$sql = "SELECT n.node_id, n.folder_id, n.module_id, n.node_action_mode, 
						n.database_id, n.params, n.permissions, b.name AS block_name
					FROM {$this->DB->prefix()}engine_nodes AS n
					LEFT JOIN {$this->DB->prefix()}engine_blocks_inherit AS bi USING (block_id)
					LEFT JOIN {$this->DB->prefix()}engine_blocks AS b USING (block_id)
					WHERE n.node_id = '$node_id'
					AND n.is_active = 1
					AND n.site_id = '{$this->Env->site_id}'
					AND b.site_id = '{$this->Env->site_id}'
					AND bi.site_id = '{$this->Env->site_id}'
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
					// @todo подумать над правами на запись в ноду, видимо можно вообще модулю не передавать эти данные.
				}

				// @todo переделать!
				switch ($_SERVER['REQUEST_METHOD']) {
					case 'POST':
					
						// @todo сделать проверку на валидность сабмита.
						// Вытаскиваем имя ключа в поле $submit
						foreach ($_POST['submit'] as $key => $dummy) {
							$submit = $key;
						}
						
						if (isset($_POST['pd'])) {
							$Module->postProcessor($_POST['pd'], $submit);
						} else {
							$Module->postProcessor(array(), $submit);
						}
					
						cmf_redirect();
						break;
					default;
				}				

				// Удаляется первый слеш и запускается действие.
				$Module->nodeAction(substr($uri_path, 1));
				
				// @todo сейчас используется блок content для popup режима. возможно надо другой заюзать, например некий 'default'.
				if ($row->node_action_mode === 'popup') {
					$this->ScriptsLib->request('jquery');
					$this->Html->addHeadScript('backend.js', HTTP_SYS_RESOURCES . 'admin/backend/backend.js');
					$this->Html->addDocumentReady('fieldsetsToTabs($j);');
					// @todo !!! сделать нормально ;) popup_iframe.css не нужен для built-in режима.
					$this->Html->addHeadStyle('system_admin.css', HTTP_SYS_RESOURCES . 'admin/backend/system_admin.css');
					$this->Html->addHeadStyle('popup_iframe.css', HTTP_SYS_RESOURCES . 'styles/popup_iframe.css');
					
					$block = 'content';
				} else {
					$block = $row->block_name;
				}
				
				$this->View->Content = $Module->View;
				
				$this->front_end_action_mode = $row->node_action_mode;
				
				// Запоминается ИД ноды, на которую применён action, дальше она собираться больше не будет.
				$this->front_end_action_node_id = $node_id;
				unset($Module);
			}
		} else {
			return false;
		}
			
		return true;
	}
	
	public function getFrontEndActionMode()
	{
		return $this->front_end_action_mode;
	}	

	public function getFrontEndActionNodeId()
	{
		return $this->front_end_action_node_id;
	}
	
}
