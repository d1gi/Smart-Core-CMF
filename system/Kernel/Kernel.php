<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Smart Core CMF (Content Managment Framework/System)
 * 
 * Ядро системы.
 * 
 * @package		Kernel
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @uses 		Cookie
 * @uses 		DB
 * @uses 		EE
 * @uses 		Environment
 * @uses 		Log
 * @uses 		Permissions
 * @uses 		Session
 * @uses 		Site
 * @uses 		User
 * 
 * @version 	2012-01-09.0
 */
class Kernel extends SingletonBase
{
	/**
	 * Информация о версии.
	 * @var string
	 */
	const VERSION		= '0.38beta';
	const VERSION_BUILD	= '169';
	const VERSION_DATE	= '2011-12-27';
	
	/**
	 * Статическая приватная ссылка подключения ядра к БД, для использования методом static getDBConnection()
	 * @access private
	 * @var object
	 */
	private static $DB_static;
	
	/**
	 * Статическая приватная ссылка на системное окружение, для использования методом static getEnv()
	 * @access private
	 * @var object
	 */
	private static $Env_static;
	
	/**
	 * В случае выполнения модулем парсинга части УРИ, результат сохраняется в статической переменной,
	 * чтобы можно было получить это значение через обращение к ядру, это нужно при хук запросам к
	 * модулям, которые обработали парсинг УРИ.
	 * @var array
	 */
	private static $parser_node_id = false;
		
	/**
	 * Свойcтво поведения дейсвия над нодой (всроенное в шаблон - 'built-it' или во всплывающем окошке - 'pupup' или 'ajax' - подгружаемое в блок размещения.).
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
	 * Список нод, полученный в результате работы функции getNodesList(), храним её в свойстве ядра, 
	 * чтобы не гонять лишний раз массив для метода buildModulesData().
	 * @access private
	 * @var array
	 */
	private $nodes_list = array();
	private static $nodes_list_static;
	
	private static $parsed_uri;
	
	/**
	 * Данные профайлера.
	 * @var array
	 */
	private $profiler = false;
	
	/**
	 * Системный конфиг.
	 * @var array
	 */
	private $config;
	
	/**
	 * Конструктор. Синглтон паттерн.
	 * 
	 * @access protected
	 * 
	 * @uses ClassLoader
	 * @uses DB
	 * @uses Environment
	 * 
	 * @param array $config
	 * @return void
	 */
	protected function __construct($config)
	{
		$this->config = $config;
		
		// Инициализация профайлера.
		if (!empty($this->config['debug_profiler'])) {
			$this->profiler = array();
		}
		
		$this->profiler('kernel', 'constructor', 1);

		// Регистрация пространств имён для автозагрузки классов.
		ClassLoader::registerNamespace(array(
				'Module' => array(
					Site::getDirApplication() . 'Modules/',
					DIR_SYSTEM . 'Modules/',
					),
				'Component' => array(
					Site::getDirApplication() . 'Components/',
					DIR_SYSTEM . 'Components/',
					),
				'Helper' => array(
					Site::getDirApplication() . 'Helpers/',
					DIR_SYSTEM . 'Helpers/',
					),
				'Plugin' => array(
					Site::getDirApplication() . 'Plugins/',
					DIR_SYSTEM . 'Plugins/',
					),
				'Zend' => array(
					DIR_ZEND_FRAMEWORK . 'library/',
					),
				)
			);
		// Регистрация изолированных неймспейсов.
		ClassLoader::registerNamespaceIsolated(array(
			'Module', 'Component', 'Helper', 'Plugin'
			));
		
		// Подключение к БД.
		$this->DB = DB::getInstance($this->config);
		// Создаётся статическая приватная ссылка подключения ядра к БД, для использования вспомогательными методами, типа static getUriByFolderId()
		self::$DB_static = &$this->DB; 
		
		// Установка системного окружения.
		$this->Env = new Environment(array(
			'dir_sites'				=> $this->config['dir_sites'],
			'site_id'				=> 0,
			'language_id'			=> 'ru',
			'default_language_id'	=> 'ru',
			'current_folder_id'		=> 1,
			'current_folder_path'	=> HTTP_ROOT,
			'cache_enable'			=> false,
			));
		self::$Env_static			= &$this->Env;
		
		self::$nodes_list_static	= &$this->nodes_list;
				
		$this->profiler('kernel', 'constructor', 0);
	}
	
	/**
	 * Запус приложения. 
	 * 
	 * 1. parser() - Парсится строка запроса.
	 * 
	 * 2. getNodesList() - Готовится список всех нужных нод, в каких контейнерах они сидят и с какими параметрами запускаются модули.
	 * 
	 * 3. buildModulesData() - Собирается массив $ЕЕ исходя из контейнеров и подготовленного списка нод, по мере 
	 *    прохождения, подключаются и запускаются нужные модули с нужными параметрами.
	 * 
	 * 4. Response->send() - Вывод данных.
	 * 
	 * @access 	public
	 * @uses 	Response
	 * @return 	void
	 */
	public function run()
	{
		$this->profiler('kernel', 'siteinit', 1);
		
		$this->siteInit();
		
		$this->profiler('kernel', 'siteinit', 0);

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
			cf_redirect();
		}
		
		$this->profiler('kernel', 'parser', 1);
		
		self::$parsed_uri = $this->parser($_SERVER['REQUEST_URI']);
//		cf_debug(self::$parsed_uri);

		$this->profiler('kernel', 'parser', 0);
		
		// Запоминается результат парсера выполненного нодой.
		if (isset(self::$parsed_uri['parser_node_id'])) {
			self::$parser_node_id = self::$parsed_uri['parser_node_id'];
		}
		
		if ($this->Response->isDirect() === false) {
			// Если есть POST данные, и они небыли обработаны в режимах ACTION, ADMIN или AJAX,
			// то запускается postProcessor, который попытается адресовать эти пост данные ноде.
			if (count($_POST) > 0) {
				$this->postProcessor();
			}
			
			$this->profiler('kernel', 'nodes_list', 1);
			
			// Сборка списка нод в запроценном ресурсе.
			$this->buildNodesList(self::$parsed_uri);
//			cf_debug($this->nodes_list, 'nodes_list');
			
			$this->profiler('kernel', 'nodes_list', 0);

			$this->profiler('kernel', 'all_nodes', 1);
			
			if ($this->Cookie->cmf_session_force_start == true) {
				$this->Session->start();
			}
			
			// На основании списка нод в $this->nodes_list запускается сборка модулей.
			$this->buildModulesData();
			
			if ($this->Cookie->cmf_session_force_start !== null) {
				$this->Session_Force->clean();
				$this->Cookie->delete('cmf_session_force_start');
			}
			
			$this->profiler('kernel', 'all_nodes', 0);
		}

		$this->profiler('kernel', 'output', 1);
		
		$this->Response->send();
		
		$this->profiler('kernel', 'output', 0);
	}
	
	/**
	 * Инициализация сайта.
	 *
	 * Выполняются следующие операции:
	 * - Авторизируется пользователь.
	 * - Инициализация системы прав доступа.
	 * 
	 * @access private
	 * 
	 * @uses Cookie
	 * @uses User
	 * 
	 * @return void
	 */
	protected function siteInit()
	{
		if (Site::init() === false) {
			// Если домен в системе не зарегистрирован, то выводится сообщение, что платформа работает и сеанс завершается.
			echo '<b>It Works!</b>';
			exit;
		}
		
		// @todo можно убрать в Site::init()
		$this->Cookie->init(array(
			'prefix'	=> Site::getCookiePrefix(),
			'path'		=> HTTP_ROOT,
			'expire'	=> 7776000, // 90 дней @todo сделать настройку времени жизни куки через конфигурирование сайта, а может быть и через настройки юзера.
			));
		// @todo хранить более полную инфу о юзере, например список групп в которых он состоит.
		$this->Env->setUserId(User::getInstance($this->config)->getId());
	}
	
	/**
	 * Запуск крон.
	 *
	 * @return void
	 */
	public function cron()
	{
		$Cron = new Cron();
		$Cron->run();
	}
	
	/**	
	 * Парсер строки запроса (URI).
	 * 
	 * + проверка на допустимые символы в строке запроса.
	 * - переключение языка через УРИ.
	 * - активация темы (тема может меняться как исходя из строки запроса, так из профиля пользователя).
	 * - обработка механизма дейсвтвия над нодой типа http://loc/smart_core/_node2/edit/.
	 * + заполняется массив $parsed_uri.
	 * + заполняется массив с хлебными крошками: $this->EE->breadcrumbs
	 * + выявление ноды, которой передаётся дальнейший парсинг УРИ.
	 * 
	 * @access protected
	 * 
	 * @uses Admin_Folder
	 * 
	 * @param string $request_uri
	 * @return array $parsed_uri
	 */
	protected function parser($request_uri)
	{
		// Отсекается подпапка в которой установлен движок.
		$tmp = parse_url(substr($request_uri, strlen(HTTP_ROOT) - 1));

		$uri_path = $tmp['path'];
		
		// Запрошен robots.txt из корня.
		if ($uri_path === '/robots.txt') {
			header('Content-Type: text/plain; charset=UTF-8');
			echo Site::getRobotsTxt();
			exit;
		}
		
		$uri_path_parts = explode('/', $uri_path);
		
		unset($tmp);
		
		// Проверка строки запроса на допустимые символы.
		// @todo сделать проверку на разрешение круглых скобок.
		foreach ($uri_path_parts as $value) {
			if (!preg_match('/^[a-z_@0-9.-]*$/iu', $value)) {
				//echo "<b>Недопустимая строка запроса: $value</b><br />";
				$this->http404();
			}
		}
		
		// Храним текущий полный путь УРИ, применяется для отрезания "лишнего" при передаче модулю.
		$current_path = ''; 
		$parsed_uri = array();
		$parser_node_id = null;
		
		// @todo Переключение языка через URI
		//$this->switchLanguageByUriPart($uri_path_parts[1]);

		$folder_pid = 0;
		$Folder = new Folder();
	
		foreach ($uri_path_parts as $key => $folder_name) {
			
			// Получен служебный запрос ACTION, ADMIN или AJAX.
			// @todo продумать как поступать, если служебный запрос встретился не в начале.
			if ($folder_name === ACTION or $folder_name === ADMIN or $folder_name === AJAX) {
				$class_name = ucfirst($folder_name);
				$SystemRequest = new $class_name();
				if ($SystemRequest->run(str_replace($current_path . $folder_name . '/', '', $uri_path))) {
					$this->front_end_action_mode = $SystemRequest->getFrontEndActionMode();
					// Запоминается ИД ноды, на которую применён action, дальше она собираться больше не будет.
					if ($SystemRequest->getFrontEndActionNodeId() !== false) {
						$this->front_end_action_node_id = $SystemRequest->getFrontEndActionNodeId();
					}
				} 
				break;
			}
			
			// заканчиваем работу, если имя папки пустое и папка не является корневой 
			// т.е. обрабатываем последнюю запись в строке УРИ
			if('' == $folder_name and 0 != $key) { 
				// @todo видимо здесь надо делать обработчик "файла" т.е. папки с выставленным флагом "is_file".
				break;
			}
			
			// В данной папке есть нода которой передаётся дальнейший парсинг URI.
			if ($parser_node_id !== null) {
				// выполняется часть URI парсером модуля и возвращается результат работы, в дальнейшем он будет передан самой ноде.
				// @todo сделать создание объекта модуля через статический вызов Ноде.
				$Node = new Node();
				$Module = $Node->getModuleInstance($parser_node_id);
				$mod_parser_data = $Module->parser(str_replace($current_path, '', $uri_path));
				unset($Module);
				
				// Парсер модуля вернул положительный ответ.
				if ($mod_parser_data !== false) {
					$parsed_uri['parser_node_id'] = array(
						'id'		=> $parser_node_id,
						'data'		=> $mod_parser_data['data'],
						'uri_part'	=> str_replace($current_path, '', $uri_path),
						);
					if (isset($mod_parser_data['breadcrumbs']) and is_array($mod_parser_data['breadcrumbs'])) {
						foreach ($mod_parser_data['breadcrumbs'] as $breadcrumb) {
							$this->EE->addBreadCrumb($breadcrumb['uri'], $breadcrumb['title'], $breadcrumb['descr']);
						}
					}
					// В случае успешного завершения парсера модуля, парсинг ядром прекращается.
					break; 
				}
			} // __end if ($parser_node_id !== NULL)
			
			$folder = $Folder->getData($folder_name, $folder_pid);
			
			if ($folder !== false) {
				if ($this->Permissions->isAllowed('folder', 'read', $folder->permissions)) {
					$this->Env->current_folder_id = $folder->folder_id;
					// Заполнение мета-тегов.
					if (!empty($folder->meta)) {
						foreach (unserialize($folder->meta) as $key => $value) {
							$this->EE->addHeadMeta($key, $value);
						}
					}
					
					if ($folder->uri_part !== '') {
						$this->Env->current_folder_path .= $folder->uri_part . '/';
					}
					
					// Чтение макета для папки.
					if (!empty($folder->layout)) {
						$this->EE->template['layout'] = $folder->layout;
					}
					
					// Чтение предствлений для папки.
					if (!empty($folder->views)) {
						$this->EE->template['views'] = $folder->views;
					}
					
					$folder_pid		= $folder->folder_id;
					$current_path	.= $folder->uri_part . '/';
					$parser_node_id = $folder->parser_node_id;
					
					$parsed_uri[$folder->folder_id] = array(
						'transmit_nodes' => $folder->transmit_nodes,
						'nodes_blocks' => unserialize($folder->nodes_blocks),
						);
					
					$this->EE->addBreadCrumb(substr(HTTP_ROOT, 0, -1) . $current_path, $folder->title, $folder->descr);
				} else {
					$this->http403();
				}
			} else {
				$this->http404();
			}
		} // __end foreach ($uri_path_parts as $key => $folder_name)
		return $parsed_uri;
	}
	
	/**	
	 * Переключение языка через URI
	 * 
	 * @access protected
	 * @param string $str
	 * 
	 * @todo сделать метод переключения языка через URI.
	 */
	protected function switchLanguageByUriPart($str)
	{
		return;
	}
	
	/**
	 * Создание списка всех запрошеных нод, в каких контейнерах они находятся и с какими 
	 * параметрами запускаются модули.
	 * 
	 * Заполняется массив nodes_list.
	 * 
	 * @access protected
	 * @param array $parsed_uri
	 * @returns $this->nodes_list
	 */
	protected function buildNodesList(array $parsed_uri)
	{
		// @todo не собираем список нод, если сработан механизи ACTIONS во всплывающем окошке.
		if ($this->front_end_action_mode === 'popup') {
			return;	
		}
		
		$nodes_blocks = array(
			'single'  => array(), // Блокировка нод в папке, без наследования.
			'inherit' => array(), // Блокировка нод в папке, с наследованием.
			'except'  => array(), // Блокировка всех нод в папке, кроме заданных.
			);
		$used_nodes = array();
		
		foreach ($parsed_uri as $folder_id => $parsed_uri_value) {
			// если есть результат рыботы парсера модуля, то выходим из цикла
			if ($folder_id === 'parser_node_id') {
				break;
			}
			
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
				$sql = "SELECT
						n.module_id,
						n.node_id,
						n.params,
						n.cache_params,
						n.plugins,
						n.database_id,
						n.permissions,
						n.is_cached,
						n.container_id AS container_id,
						n.node_action_mode
					FROM {$this->DB->prefix()}engine_nodes AS n,
						{$this->DB->prefix()}engine_containers_inherit AS ci
					WHERE n.container_id = ci.container_id 
						AND is_active = 1
						AND n.folder_id = '{$folder_id}'
						AND ci.folder_id = '{$folder_id}'
						AND n.site_id = '{$this->Env->site_id}'
						AND ci.site_id = '{$this->Env->site_id}'
					ORDER BY n.pos ";
			}
			
			// Обрабатываем последнюю папку т.е. текущую.
			if ($folder_id == $this->Env->current_folder_id) {
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
				$this->nodes_list[$row->node_id] = array(
					'folder_id'		=> $folder_id,
					'module_id'		=> $row->module_id,
					'container_id'	=> $row->container_id,
					'params'		=> $row->params,
					'cache_params'	=> $row->cache_params,
					'is_cached'		=> $row->is_cached,
					'plugins'		=> $row->plugins,
					'permissions'	=> $row->permissions,
					'parser_data'	=> null, // В случае, если не был отработан механизм парсинга строки запроса модулем, считаеся, что парсер данных не вернул ничего либо вернул NULL.
					'database_id'	=> $row->database_id,
					'node_action_mode' => $row->node_action_mode,
					);
			}
		}
		
		foreach ($nodes_blocks['single'] as $node_id => $value) {
			unset($this->nodes_list[$node_id]);
		}
		
		foreach ($nodes_blocks['inherit'] as $node_id => $value) {
			unset($this->nodes_list[$node_id]);
		}
		
		if (!empty($nodes_blocks['except'])) {
			foreach ($this->nodes_list as $node_id => $value) {
				if (!array_key_exists($node_id, $nodes_blocks['except'])) {
					unset($this->nodes_list[$node_id]);
				}
			}
		}
		
		// В случае, если парсер модуля отработал успешно, его ноде передаются данные, которые он вернул.
		if (isset($parsed_uri['parser_node_id'])) {
			$this->nodes_list[$parsed_uri['parser_node_id']['id']]['parser_data'] = $parsed_uri['parser_node_id']['data'];
		}
	}	
	
	/**
	 * Собирается массив $ЕЕ, исходя из контейнеров и подготовленного списка нод,
	 * по мере прохождения, подключаются и запускаются нужные модули с нужными параметрами.
	 * 
	 * @uses Module_*
	 * 
	 * @returns $this->EE->data
	 * @returns $this->EE->admin['frontend']
	 */
	protected function buildModulesData()
	{
		$containers = array();

		// Создаётся список всех доступных контейнеров в системе.
		$sql = "SELECT container_id, name
			FROM {$this->DB->prefix()}engine_containers
			WHERE site_id = '{$this->Env->site_id}'
			ORDER BY pos ASC ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$containers[$row->container_id] = $row->name;
		}		

		$Node = new Node();
		foreach ($this->nodes_list as $node_id => $node_properties) {
			// Не собираем ноду, если она уже была отработала в механизе nodeAction()
			if ($node_id == $this->front_end_action_node_id) {
				continue;
			}
			
			$this->profiler('node', $node_id, 1);
			
			$container_name = $containers[$node_properties['container_id']];

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
							case 'parser_data':
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
				$this->EE->data[$container_name][$node_id]['html_cache'] = $html_cache;
			}
			// Кеша нет.
			else { 
				// Если разрешены права на запись ноды, то создаётся объект с административными методами и запрашивается у него данные для фронтальных элементов управления.
				if ($this->Permissions->isAllowed('node', 'write', $node_properties['permissions']) and ($this->Permissions->isRoot() or $this->Permissions->isAdmin()) ) {
					$Module = $Node->getModuleInstance($node_id, true);
				} else {
					$Module = $Node->getModuleInstance($node_id, false);
				}
		
				$Module->run($node_properties['parser_data']);

				// Результат работы модуля записывается в ЕЕ.
				
				$this->EE->data[$container_name][$node_id] = array (
					'tpl_path'			=> $Module->getTplPath(),
					'tpl'				=> $Module->getTpl(),
					'tpl_path_priority'	=> 'auto',
					'node_action_mode'	=> $node_properties['node_action_mode'], // @todo убрать.
					);

				// Указать шаблонизатору, что надо сохранить эту ноду как html.
				if (_IS_CACHE_NODES and !empty($cache_params) and $this->Cookie->cmf_frontend_mode !== 'edit') { // @todo ВАЖНО подумать как тут поступить т.к. эта кука может стоять у гостя!!!
					$this->EE->data[$container_name][$node_id]['store_html_cache'] = $Module->getCacheParams($cache_params);
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
				}

				// Запрашивается у модуля его данные и сохраняются в EE->data[контейнер][ID ноды]['data'].
				$this->EE->data[$container_name][$node_id]['data'] = $Module->getOutputData();
			}
			
			$this->profiler('node', $node_id, 0);
			
			if ($this->profiler !== false) {
				$this->profiler['node'][$node_id]['module'] = $node_properties['module_id'];
				$this->EE->data[$container_name][$node_id]['profiler'] = $this->profiler['node'][$node_id];
			}
			unset($Module);
		}
		unset($Node);
	}
		
	/**
	 * Метод работы с POST данными.
	 * 
	 * При обнаружении POST данных, выполняется следующий алгоритм ядра:
	 * - проверяется имеет ли право на запись данный пользователь.
	 */
	protected function postProcessor()
	{
//		cf_debug($_SESSION); cf_debug($_POST); exit;
		
		// POST данные, адресованные модулю.
		if (isset($_POST['node_id'])) {
			$node_id = $_POST['node_id']; 
			
			// @todo пока так, а дальше сделать более интеллектуальную инвалидацию.
			$this->Cache->updateNode($node_id);
			
			// если node_id не число, то редиректимся обратно.
			// @todo добавить обработку ошибок
			if (!is_numeric($node_id)) {
				cf_redirect();
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
					// $Module->setWritable(true);
		
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
			cf_redirect($_POST['return_to']);
		} else {
			cf_redirect();
		}
	}
	
	/**
	 * Обработчик ошибки 404
	 * 
	 * @uses Log
	 * 
	 * @todo сделать нормальный настраиваеный вывод сообщения об ошибке.
	 */
	public function http404()
	{
		$this->Response->headerStatus(404);
		Log::getInstance()->write('http_error', 404);
		echo 'Error 404';
		exit;
	}
		
	/**
	 * Обработчик ошибки 403
	 * 
	 * @uses Log
	 * 
	 * @todo сделать нормальный настраиваеный вывод сообщения об ошибке.
	 */
	public function http403()
	{
		$this->Response->headerStatus(403);
		Log::getInstance()->write('http_error', 403);
		echo 'Error 403';
		exit;
	}
	
	/**
	 * Получить подключение ядра к БД.
	 *
	 * @return resource object
	 */
	public static function getDBConnection()
	{
		return self::$DB_static;
	}
	
	/**
	 * Получение ссылки на Env.
	 * 
	 * @return mixed
	 */
	static public function getEnv($varname = null)
	{
		return $varname === null ? self::$Env_static : self::$Env_static->$varname;
	}
	
	/**
	 * Получить данные ноды.
	 * Учитываются только те ноды, которые были собраны методом buldNodesList() иначе возвращется false.
	 *
	 * @param int $node_id
	 * @return array|null
	 */
	public static function getNodeData($node_id)
	{
		return array_key_exists($node_id, self::$nodes_list_static) ? self::$nodes_list_static[$node_id] : null;
	}
	
	/**
	 * Получить список нод, учавствующих в запросе.
	 *
	 * @param int $node_id
	 * @return array|false
	 */
	public static function getNodesList()
	{
		return self::$nodes_list_static;
	}
	
	/**
	 * Получить данные результата парсера части УРИ, которые обработала нода.
	 *
	 * @return array
	 */
	public static function getParserNodeData()
	{
		return self::$parser_node_id;
	}
	
	/**
	 * Получить данные результата парсера.
	 *
	 * @return array
	 */
	public static function getParserData()
	{
		return self::$parsed_uri;
	}
	
	/**
	 * Профилировщик.
	 *
	 * @param string $tag - Тэг, например kernel или node.
	 * @param string $name - Имя ключа, наприме init или номер ноды.
	 * @param string $action - Дейсвтвие.
	 * 			1 или true - Запуск отслеживания.
	 * 			0 или false - Остановка отслеживания.
	 * @return void
	 * 
	 * @todo вынести в отдельный класс.
	 */
	protected function profiler($tag, $name, $action)
	{
		if ($this->profiler === false) {
			return null;
		}
		
		if (count($this->profiler) == 0) {
			$this->profiler['preloading'] = array(
				'memory' => memory_get_usage(),
				'time' => microtime(true) - START_TIME,
				);
		}
		
		// Засекание времени
		if ($action) {
			$this->profiler[$tag][$name]['start_memory'] = memory_get_usage();
			$this->profiler[$tag][$name]['start_time'] = microtime(true);
			$this->profiler[$tag][$name]['start_db_count'] = DB::getQueryCount();
			
			if (DEBUG_DB_QUERY and $name !== 'all_nodes') {
				$this->profiler[$tag][$name]['start_db_query_log'] = count(DB::$query_log);
			}
		} 
		// Вычисление дельты.
		else { 
			$this->profiler[$tag][$name]['memory'] = memory_get_usage() - $this->profiler[$tag][$name]['start_memory'];
			$this->profiler[$tag][$name]['time'] = microtime(true) - $this->profiler[$tag][$name]['start_time'];
			$this->profiler[$tag][$name]['db_query_count'] = DB::getQueryCount() - $this->profiler[$tag][$name]['start_db_count'];
			if (DEBUG_DB_QUERY and $name !== 'all_nodes') {
				$cnt = 1;
				$num = $this->profiler[$tag][$name]['start_db_count'];
				while ($num < count(DB::$query_log)) {
					$this->profiler[$tag][$name]['db_query_log'][$cnt++] = DB::$query_log[$num++];
				}
			}
			unset(
				$this->profiler[$tag][$name]['start_time'], 
				$this->profiler[$tag][$name]['start_db_count'], 
				$this->profiler[$tag][$name]['start_db_query_log'], 
				$this->profiler[$tag][$name]['start_memory']
			);
		}
	}
	
	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		if ($this->profiler !== false) {
			echo "\n<pre>\n" . round(microtime(true) - START_TIME, 4) . " сек, \n";
			echo cf_format_filesize(memory_get_peak_usage(true)) . " (" . memory_get_peak_usage(true) . " байт) памяти, \n";
			echo DB::getQueryCount() . " запросов в БД\n</pre>\n";			
		}
		
//		cf_debug($_COOKIE, '_COOKIE');
//		@cf_debug($_SESSION, '$_SESSION');
		
//		unset($this->EE->data['v-menu']);
//		$this->EE->admin = null;
//		$this->EE->data = null;
//		unset($this->EE->data);

//		cf_debug($this->profiler, 'profiler');
//		cf_debug(DB::$query_log, 'DB::$query_log');
//		cf_debug(DB::$query_log_profiler, 'DB::$query_log_profiler');
//		cf_debug(DB::getStat(), 'DB::getStat()');
//		cf_debug(DB::getQueryesDublicates(), 'getQueryesDublicates:');
//		cf_debug($this->profiler['node'][8]); // меню
//		cf_debug($this->profiler['kernel']['parser']);

//		cf_debug($this->EE);
							
//		cf_debug($this->EE->template, 'Массив template');
//		cf_debug($this->EE->data, 'Массив data');
//		cf_debug($this->EE->data['content'], 'Контейнер "content"');
//		cf_debug($this->EE->data['v-menu'], 'Контейнер "v-menu"');
//		cf_debug($this->EE->admin['toolbar']);
//		cf_debug($this->Session);
//		cf_debug($this->Cookie);
		
	}	
}