<?php
/**
 * NodeMapper - Управляющий контроллер.
 * 
 * @author		Artem Ryzhkov
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @uses		Cookie
 * @uses		DB
 * @uses		EE
 * @uses		Env
 * @uses		Session_Force
 * @uses		View
 * 
 * @version 	2012-01-31.0
 */
class NodeMapper extends Controller
{
	/**
	 * Свойcтво поведения действия над нодой (всроенное в шаблон - 'built-it' или во всплывающем окошке - 'pupup' или 'ajax' - подгружаемое в блок размещения.).
	 * По умолчанию инициализируется как false.
	 * 
	 * @access private
	 * 
	 * @todo надо подумать, надо ли вообще это держать в кернеле?
	 */	
	private $front_end_action_mode = false;
	
	/**
	 * @access private
	 */
	private $front_end_action_node_id = 0;
	
	/**
	 * NewFunction
	 *
	 * @param
	 */
	public function run($params)
	{
		View::prependPath(DIR_ROOT . 'themes/default/tpl/'); // @todo
		
		$this->profilerStart('node_mapper', 'build_nodes_list');
		$nodes_list = $this->buildNodesList($params['folders']);
		$this->profilerStop('node_mapper', 'build_nodes_list');
		
		switch ($_SERVER['REQUEST_METHOD']) {
			case 'GET':
				// Сразу собираются хлебные крошки т.к. модули могут юзать их, например меню вычисляет по ним активные ссылки на папки.
				foreach ($params['folders'] as $folder) {
					$this->Breadcrumbs->add($folder['uri'], $folder['title'], $folder['descr']);
					if (isset($folder['route']['breadcrumbs']) and !empty($folder['route']['breadcrumbs'])) {
						foreach ($folder['route']['breadcrumbs'] as $bc) {
							$this->Breadcrumbs->add($bc['uri'], $bc['title'], $bc['descr']);
						}
					}
				}	
				
				$this->EE->template['views'] = $params['views']; // @todo убрать
				
				$this->View = new Html();
				$this->View->setTpl($params['layout']);
				$this->View->setTplPath('layouts');
				$this->View->Blocks = new View();
				
				if ($this->Cookie->cmf_session_force_start == true) {
					$this->Session->start();
				}

				$this->profilerStart('node_mapper', 'build_modules_data');
				$this->buildModulesData($nodes_list);
				$this->profilerStop('node_mapper', 'build_modules_data');
				
				if ($this->Cookie->cmf_session_force_start == true) {
					$this->Session_Force->clean();
					$this->Cookie->delete('cmf_session_force_start');
				}					
				break;
			case 'POST':
				$this->postProcessor();
				break;
			default;
		}
	}
	
	/**
	 * Создание списка всех запрошеных нод, в каких блоках они находятся и с какими 
	 * параметрами запускаются модули.
	 * 
	 * @access protected
	 * @param array $parsed_uri
	 * @return array $nodes_list
	 */
	protected function buildNodesList(array $folders)
	{
		$nodes_list = array();
		
		// @todo не собирать список нод, если сработан механизи ACTIONS во всплывающем окошке.
		if ($this->front_end_action_mode === 'popup') {
			return;	
		}

		$nodes_blocks = array(
			'single'  => array(), // Блокировка нод в папке, без наследования.
			'inherit' => array(), // Блокировка нод в папке, с наследованием.
			'except'  => array(), // Блокировка всех нод в папке, кроме заданных.
			);
		$used_nodes = array();
		
		foreach ($folders as $folder_id => $parsed_uri_value) {
			// single каждый раз сбрасывается и устанавливается заново для каждоый папки.
			$nodes_blocks['single'] = array();
			if (isset($parsed_uri_value['nodes_blocks']['single']) and !empty($parsed_uri_value['nodes_blocks']['single'])) {
				//$nodes_blocks['single'] = $parsed_uri_value['nodes_blocks']['single'];
				$tmp = explode(',', $parsed_uri_value['nodes_blocks']['single']);
				foreach ($tmp as $single_value) {
					$t = trim($single_value);
					if (!empty($t)) {
						$nodes_blocks['single'][trim($single_value)] = 'blocked'; // ставлю тупо 'blocked', но главное в массиве с блокировками, это индексы.
					}
				}
			}
			
			// Блокировка нод в папке, с наследованием.
			if (isset($parsed_uri_value['nodes_blocks']['inherit']) and !empty($parsed_uri_value['nodes_blocks']['inherit'])) {
				$tmp = explode(',', $parsed_uri_value['nodes_blocks']['inherit']);
				foreach ($tmp as $inherit_value) {
					$t = trim($inherit_value);
					if (!empty($t)) {
						$nodes_blocks['inherit'][trim($inherit_value)] = 'blocked'; // ставлю тупо 'blocked', но главное в массиве с блокировками, это индексы.
					}
				}
			}
			
			// Блокировка всех нод в папке, кроме заданных.
			if (isset($parsed_uri_value['nodes_blocks']['except']) and !empty($parsed_uri_value['nodes_blocks']['except'])) {
				$tmp = explode(',', $parsed_uri_value['nodes_blocks']['except']);
				foreach ($tmp as $except_value) {
					$t = trim($except_value);
					if (!empty($t)) {
						$nodes_blocks['except'][trim($except_value)] = 'blocked'; // ставлю тупо 'blocked', но главное в массиве с блокировками, это индексы.
					}
				}
			}
			
			$sql = false;
			if ($parsed_uri_value['transmit_nodes'] == 1) { // в этой папке есть ноды, которые наследуются...
				$sql = "SELECT n.module_id, n.node_id, n.params, n.cache_params, n.plugins, n.database_id, 
						n.permissions, n.is_cached, n.block_id AS block_id,	n.node_action_mode
					FROM {$this->DB->prefix()}engine_nodes AS n,
						{$this->DB->prefix()}engine_blocks_inherit AS bi
					WHERE n.block_id = bi.block_id 
						AND is_active = 1
						AND n.folder_id = '{$folder_id}'
						AND bi.folder_id = '{$folder_id}'
						AND n.site_id = '{$this->Env->site_id}'
						AND bi.site_id = '{$this->Env->site_id}'
					ORDER BY n.pos ";
			}
			
			// Обрабатываем последнюю папку т.е. текущую.
			if ($folder_id == $this->Env->current_folder_id) { // @todo убрать Env
				$sql = "SELECT * FROM {$this->DB->prefix()}engine_nodes WHERE folder_id = '{$folder_id}' AND is_active = '1' AND site_id = '{$this->Env->site_id}' ";
				// исключаем ранее включенные ноды.
				foreach ($used_nodes as $used_nodes_value) {
					$sql .= " AND node_id != '{$used_nodes_value}'";
				}
				$sql .= ' ORDER BY pos';
			}
			
			// В папке нет нод для сборки.
			if ($sql === false) {
				continue;
			}

			$result = $this->DB->query($sql);
			while ($row = $result->fetchObject()) {
				if ($this->Permissions->isAllowed('node', 'read', $row->permissions) == 0) {
					continue;
				}

				// Создаётся список нод, которые уже в включены.
				if ($parsed_uri_value['transmit_nodes'] == 1) { 
					$used_nodes[] = $row->node_id; 
				}

				$nodes_list[$row->node_id] = array(
					'folder_id'		=> $folder_id,
					'module_id'		=> $row->module_id,
					'block_id'		=> $row->block_id,
					'params'		=> $row->params,
					'cache_params'	=> $row->cache_params,
					'is_cached'		=> $row->is_cached,
					'plugins'		=> $row->plugins,
					'permissions'	=> $row->permissions,
					'route_params'	=> null, // В случае, если не был отработан механизм парсинга строки запроса модулем, считаеся, что парсер данных не вернул ничего либо вернул NULL.
					'database_id'	=> $row->database_id,
					'node_action_mode' => $row->node_action_mode,
					);
			}
			
			if (isset($parsed_uri_value['route'])) {
				$nodes_list[$parsed_uri_value['route']['node_id']]['route_params'] = $parsed_uri_value['route'];
			}
		}
		
		foreach ($nodes_blocks['single'] as $node_id => $value) {
			unset($nodes_list[$node_id]);
		}
		
		foreach ($nodes_blocks['inherit'] as $node_id => $value) {
			unset($nodes_list[$node_id]);
		}
		
		if (!empty($nodes_blocks['except'])) {
			foreach ($nodes_list as $node_id => $value) {
				if (!array_key_exists($node_id, $nodes_blocks['except'])) {
					unset($nodes_list[$node_id]);
				}
			}
		}
		
		return $nodes_list;
	}	
	
	/**
	 * Собирается массив $ЕЕ, исходя из блоков и подготовленного списка нод,
	 * по мере прохождения, подключаются и запускаются нужные модули с нужными параметрами.
	 * 
	 * @uses Module_*
	 */
	protected function buildModulesData($nodes_list)
	{
		$blocks = array();

		// Создаётся список всех доступных блоков в системе.
		$sql = "SELECT block_id, name
			FROM {$this->DB->prefix()}engine_blocks
			WHERE site_id = '{$this->Env->site_id}'
			ORDER BY pos ASC ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$blocks[$row->block_id] = $row->name;
			$name = $row->name;
			$this->View->Blocks->$name = new View();
		}
		
		$Node = new Node();
		foreach ($nodes_list as $node_id => $node_properties) {
			// Не собираем ноду, если она уже была отработала в механизе nodeAction()
			if ($node_id == $this->front_end_action_node_id) {
				continue;
			}
			
			$this->profilerStart('node', $node_id);
			
			$block_name = $blocks[$node_properties['block_id']];

			// Обнаружены параметры кеша.
			if (_IS_CACHE_NODES and $node_properties['is_cached'] and !empty($node_properties['cache_params']) and $this->Env->cache_enable ) {
				$cache_params = unserialize($node_properties['cache_params']);
				if (isset($cache_params['id']) and is_array($cache_params['id'])) {
					$cache_id = array();
					foreach ($cache_params['id'] as $key => $dummy) {
						switch ($key) {
							case 'current_folder_id':
								$cache_id['current_folder_id'] = $this->Env->current_folder_id;
								break;
							case 'user_id':
								$cache_id['user_id'] = $this->Env->user_id;
								break;
							case 'parser_data': // @todo route_data
								$cache_id['parser_data'] = $node_properties['parser_data'];
								break;
							case 'request_uri':
								$cache_id['parser_data'] = $_SERVER['REQUEST_URI'];
								break;
							case 'user_groups':
								$user_data = $this->User->getData();
								$cache_id['user_groups'] = $user_data['groups'];
								break;
							default;
						}
					}
					$cache_params['id'] = $cache_id;
				}
				$cache_params['id']['node_id'] = $node_id;
				$cache_params['nodes'][$node_id] = 1;
			} else {
				$cache_params = null;
			}

			// Попытка взять HTML кеш ноды.
			if (_IS_CACHE_NODES
				and !empty($cache_params)
				and $this->Cookie->cmf_frontend_mode !== 'edit'
				and $html_cache = $this->Cache_Node->loadHtml($cache_params['id'])
			) {
				// $this->EE->data[$block_name][$node_id]['html_cache'] = $html_cache; @todo !!!!!!!!
			}
			// Кеша нет.
			else { 
				// Если разрешены права на запись ноды, то создаётся объект с административными методами и запрашивается у него данные для фронтальных элементов управления.
				if ($this->Permissions->isAllowed('node', 'write', $node_properties['permissions']) and ($this->Permissions->isRoot() or $this->Permissions->isAdmin()) ) {
					$Module = $Node->getModuleInstance($node_id, true);
				} else {
					$Module = $Node->getModuleInstance($node_id, false);
				}
				
				if (empty($node_properties['route_params'])) {
					$Module->run($node_properties['route_params']);
				} else {
					$Module->$node_properties['route_params']['action']($node_properties['route_params']['params']);
				}
				
				// Указать шаблонизатору, что надо сохранить эту ноду как html.
				// @todo ПЕРЕДЕЛАТЬ!!! подумать где выполнять кеширование, внутри объекта View или где-то снаружи.
				// @todo ВАЖНО подумать как тут поступить т.к. эта кука может стоять у гостя!!!
				if (_IS_CACHE_NODES and !empty($cache_params) and $this->Cookie->cmf_frontend_mode !== 'edit') {
//					$this->EE->data[$block_name][$node_id]['store_html_cache'] = $Module->getCacheParams($cache_params);
				} 

				// Получение данных для фронт-админки ноды.
				// @todo сделать нормальную проверку на возможность управления нодой. сейчас пока считается, что юзер с ИД = 1 имеет право админить.
				// @todo также тут надо учитывать режим Фронт-Админки. если он выключен, то вытягивать фронт-контролсы нет смысла.
				if ($this->Permissions->isAllowed('node', 'write', $node_properties['permissions']) and $this->Cookie->cmf_frontend_mode == 'edit') {

					$front_controls = $Module->getFrontControls();
					
					// Для рута добавляется пунктик "свойства ноды"
					if ($this->Permissions->isRoot()) {
						$front_controls['_node_properties'] = array(
							'popup_window_title' => 'Свойства ноды' . " ( $node_id )",
							'title'				 => 'Свойства',
							'link'				 => HTTP_ROOT . ADMIN . '/structure/node/' . $node_id . '/?popup',
							'ico'				 => 'edit',
						);
					}

					if(is_array($front_controls)) {
						// @todo сделать выбор типа фронт админки popup/built-in/ajax.
						$this->EE->admin['frontend'][$node_id] = array(
							// 'type' => 'popup',
							'node_action_mode'	=> $node_properties['node_action_mode'],
							'doubleclick'		=> '@todo двойной щелчок по блоку НОДЫ.',
							'default_action'	=> $Module->getFrontControlsDefaultAction(),
							// элементы управления, относящиеся ко всей ноде.
							'controls'			=> $front_controls,
							// элементы управления блоков внутри ноды.
							//'controls_inner_default_action' = $Module->getFrontControlsInnerDefaultAction(),
							'controls_inner'	=> $Module->getFrontControlsInner(),
							);
					}
					
					// @todo пока так выставляются декораторы обрамления ноды.
					$Module->View->setDecorators("<div class=\"cmf-frontadmin-node\" id=\"_node$node_id\">", "</div>");
				}
			}
			
			$this->View->Blocks->$block_name->$node_id = $Module->View;

			$this->profilerStop('node', $node_id);
			unset($Module);
		}
		unset($Node);
	}
	
	/**
	 * NewFunction
	 */
	public function postProcessor()
	{
//		cmf_dump($this->Session); cmf_dump($_SESSION); cmf_dump($_POST); exit; 

		// @todo пока тут будет переключение режима фронт-админки... УБРАТЬ отсюда!!!
		// Режим работы фронт енда (просомотр или редактирование)
		if (isset($_POST['frontend_mode'])) {
			switch ($_POST['frontend_mode']) {
				case 'edit':
					$this->Cookie->cmf_frontend_mode = 'edit';
					break;
				case 'view':
					$this->Cookie->cmf_frontend_mode = 'view';
					break;
				default;
			}
			cmf_redirect();
		}
		
		if (isset($_POST['node_id'])) {
			$node_id = $_POST['node_id']; 
			
			// @todo пока так, а дальше сделать более интеллектуальную инвалидацию.
			$this->Cache->updateNode($node_id);
			
			// если node_id не число, то редиректимся обратно.
			// @todo добавить обработку ошибок
			if (!is_numeric($node_id)) {
				cmf_redirect();
			}
			
			$sql = "SELECT permissions
				FROM {$this->DB->prefix()}engine_nodes
				WHERE is_active = '1'
				AND node_id = '$node_id'
				AND site_id = '{$this->Env->site_id}' ";
			if ($row = $this->DB->getRowObject($sql)) {
				// Если пользователь иммет права на "запись" в ноду, тогда обработка POST данных передаётся модулю.
				if ($this->Permissions->isAllowed('node', 'write', $row->permissions)) {
					$Node = new Node();
					$Module = $Node->getModuleInstance($node_id, true);
		
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

					unset($Module);
				}
			}
		}
		
		if (isset($_POST['return_to'])) {
			cmf_redirect($_POST['return_to']);
		} else {
			cmf_redirect();
		}
	}
}