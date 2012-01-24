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
 * @uses 		Environment
 * @uses 		Log
 * @uses 		Permissions
 * @uses 		Site
 * @uses 		User
 * 
 * @version 	2012-01-25.0
 */
class Kernel extends Base
{
	/**
	 * Информация о версии.
	 * @var string
	 */
	const VERSION		= '0.40beta';
	const VERSION_BUILD	= '175';
	const VERSION_DATE	= '2012-01-25';
	
	/**
	 * Подключения ядра к БД.
	 * @access private
	 * @var object
	 */
	protected $DB;
	
	/**
	 * В случае выполнения модулем парсинга части УРИ, результат сохраняется в статической переменной,
	 * чтобы можно было получить это значение через обращение к ядру, это нужно при хук запросам к
	 * модулям, которые обработали парсинг УРИ.
	 * @var array
	 */
	private static $parser_node_id = false;
		
	/**
	 * Список нод, полученный в результате работы функции getNodesList(), храним её в свойстве ядра, 
	 * чтобы не гонять лишний раз массив для метода buildModulesData().
	 * @access private
	 * @var array
	 * 
	 * @todo УБРАТЬ отсюда! сейчас применяется только для учета кеширования.
	 */
	private $nodes_list = array();
	private static $nodes_list_static;
	
	private $parsed_uri;

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
	 * @uses Class_Loader
	 * @uses DB
	 * @uses Environment
	 * 
	 * @param array $config
	 */
	public function __construct($config)
	{
		$this->config = $config;
		
		// Включение профайлера.
		if (!empty($this->config['debug_profiler'])) {
			parent::$profiler_enable = true;
		}
		
		$this->profilerStart('kernel', 'constructor');

		// Регистрация пространств имён для автозагрузки классов.
		Class_Loader::registerNamespace(array(
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
		Class_Loader::registerNamespaceIsolated(array(
			'Module', 'Component', 'Helper', 'Plugin'
			));
		
		// Подключение к БД.
		$this->DB = DB::connect($this->config);
		Registry::set('DB', $this->DB);
		
		// Установка системного окружения.
		$this->Env = Environment::getInstance(array(
			'dir_sites'				=> $this->config['dir_sites'],
			'site_id'				=> 0,
			'language_id'			=> 'ru',
			'default_language_id'	=> 'ru',
			'current_folder_id'		=> 1,
			'current_folder_path'	=> HTTP_ROOT,
			'cache_enable'			=> false,
			));
		
		self::$nodes_list_static	= $this->nodes_list; // @todo убрать
				
		$this->profilerStop('kernel', 'constructor');
	}
	
	/**
	 * Запус приложения. 
	 * 
	 * @access 	public
	 * @uses 	Response
	 */
	public function run()
	{
		$this->profilerStart('kernel', 'siteinit');
		$this->siteInit();
		$this->profilerStop('kernel', 'siteinit');

		$this->profilerStart('kernel', 'router');
		
		// Роутинг.
		$route = $this->router($_SERVER['REQUEST_URI']);
		header('Content-Type: text/plain; charset=UTF-8'); // @todo убрать ;)
//		cmf_dump($route, '$route');
		
		View::setPaths(array(
			Site::getDirApplication(),
			DIR_SYSTEM,
			));
		
		// Заполнение информации о текущей "папке" в системном окружении.
		$current_folder = end($route['params']['folders']);
		$this->Env->current_folder_id	= key($route['params']['folders']);
		$this->Env->current_folder_path	= $current_folder['uri'];
		unset($current_folder);
		
		$this->profilerStop('kernel', 'router');

		$Controller = new $route['controller'];
		
		if ($route['status'] == 200) {
			$Controller->$route['action']($route['params']);
		}

		// @todo сделать норманый вывод ошибок!
		if ($route['status'] == 403) {
			$this->http403();
		}
		
		if ($route['status'] == 404) {
			$this->http404();
		}
		
		$this->profilerStart('kernel', 'output');
		
//		$this->Response->sendHeaders();
		$this->Response->send($Controller->View);

//		cmf_dump(View::getPaths());
//		cmf_dump($Controller->View);
//		cmf_dump($this->Breadcrumbs->get());
		
		$this->profilerStop('kernel', 'output');
	}
	
	/**
	 * Роутинг.
	 * 
	 * Ядро может разруливать только по 4-ём управляющим контроллерам, это:
	 *  - NodeMapper Типовой режим в котором генерируется HTML на базе модулей.
	 *  - Admin		 Администранивный режим управления системой.
	 *  - Action	 Администранивный режим управления контентом сайта.
	 *  - Ajax		 Порт для обращения к нодам и системным классам через Ajax запросы.	
	 *
	 * @param string $path
	 * @return array
	 */
	public function router($path)
	{
		// Отсекается подпапка в которой установлен движок.
		$path = parse_url(substr($path, strlen(HTTP_ROOT) - 1));
		$path = $path['path'];
		
		// Запрошен robots.txt из корня.
		if ($path === '/robots.txt') {
			header('Content-Type: text/plain; charset=UTF-8');
			echo Site::getRobotsTxt();
			exit;
		}
		
		$route = array(
			'status'		=> 200,
			'controller'	=> 'NodeMapper',	// По умолчанию NodeMapper.
			'action'		=> 'run', 			// Метод по умолчанию: run();
			'path'			=> $path,
			'params'		=> array(),
			);
		$current_folder_path = HTTP_ROOT;
		$parser_node_id = null;
		$folder_pid = 0;
		$Folder = new Folder();

		$path_parts = explode('/', $path);
		foreach ($path_parts as $key => $segment) {
			if ($key == 1 and $segment === ADMIN) {
				$route['controller'] = 'Admin';
				$Controller = new Admin();
				$route['params'] += $Controller->router(str_replace($current_folder_path . $segment . '/', '', substr(HTTP_ROOT, 0, -1) . $path));
				break;
			}
			
			if ($segment == ACTION) {
				if (isset($path_parts[$key + 1]) and is_numeric($path_parts[$key + 1])) {
					$route['controller'] = 'Action';
					$Controller = new Action();
					$route['params']['action_path'] = str_replace($current_folder_path . $segment . '/' , '', substr(HTTP_ROOT, 0, -1) . $path);
					break;
				}
			}

			// заканчиваем работу, если имя папки пустое и папка не является корневой 
			// т.е. обрабатываем последнюю запись в строке УРИ
			if('' == $segment and 0 != $key) { 
				// @todo видимо здесь надо делать обработчик "файла" т.е. папки с выставленным флагом "is_file".
				break;
			}
			
			// В данной папке есть нода которой передаётся дальнейший парсинг URI.
			if ($parser_node_id !== null) {
				// выполняется часть URI парсером модуля и возвращается результат работы, в дальнейшем он будет передан самой ноде.
				// @todo сделать создание объекта модуля через статический вызов Ноде.
				$Node = new Node();
				$Module = $Node->getModuleInstance($parser_node_id);
				$module_route = $Module->router(str_replace($current_folder_path, '', substr(HTTP_ROOT, 0, -1) . $path));
				unset($Module);
				
				// Парсер модуля вернул положительный ответ.
				if ($module_route !== false) {
					$route['params']['folders'][$folder->folder_id]['route'] = $module_route;
					$route['params']['folders'][$folder->folder_id]['route']['node_id'] = $parser_node_id;
					// В случае успешного завершения роутера модуля, роутинг ядром прекращается.
					break; 
				}				
			} // __end if ($parser_node_id !== NULL)
						
			$folder = $Folder->getData($segment, $folder_pid);
			
			if ($folder !== false) {
				if ($this->Permissions->isAllowed('folder', 'read', $folder->permissions)) {
					// Заполнение мета-тегов.
					if (!empty($folder->meta)) {
						foreach (unserialize($folder->meta) as $key => $value) {
							$route['params']['meta'][$key] = $value;
						}
					}
					
					if ($folder->uri_part !== '') {
						$current_folder_path .= $folder->uri_part . '/';
					}
					
					// Чтение макета для папки. // @todo возможно ненадо. оставить только один view.
					if (!empty($folder->layout)) {
						$route['params']['layout'] = $folder->layout;
					}
					
					// Чтение предствлений для папки. // @todo возможно ненадо.
					if (!empty($folder->views)) {
						$route['params']['views'] = $folder->views;
					}
					
					$folder_pid		= $folder->folder_id;
					$parser_node_id = $folder->parser_node_id;			
					$route['params']['folders'][$folder->folder_id] = array(
						'uri'	=> $current_folder_path,
						'title'	=> $folder->title,
						'descr'	=> $folder->descr,
						'transmit_nodes' => $folder->transmit_nodes,
						'nodes_blocks'	 => unserialize($folder->nodes_blocks),
						);
				} else {
					$route['status'] = 403;
				}
			} else {
				$route['status'] = 404;
			}
		}
		return $route;
	}
	
	/**
	 * Инициализация сайта.
	 *
	 * Выполняются следующие операции:
	 * - Авторизируется пользователь.
	 * - Инициализация системы прав доступа.
	 * 
	 * @access protected
	 * 
	 * @uses Cookie
	 * @uses User
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
		Registry::set('User', new User($this->config));
		$this->Env->setUserId($this->User->getId());
	}
	
	/**
	 * Запуск крон.
	 */
	public function cron()
	{
		$Cron = new Cron();
		$Cron->run();
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
	 * Получить данные результата парсера.
	 *
	 * @return array
	 * 
	 * @todo должен быть getRouterData(), а вообще видимо недолжно быть такого метода вообще...
	 */
	public function getParserData()
	{
		return $this->parsed_uri;
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
		$this->Response->sendHttpStatusCode(404);
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
		$this->Response->sendHttpStatusCode(403);
		Log::getInstance()->write('http_error', 403);
		echo 'Error 403';
		exit;
	}
	
	/**
	 * Получить данные ноды.
	 * Учитываются только те ноды, которые были собраны методом buldNodesList() иначе возвращется false.
	 *
	 * @param int $node_id
	 * @return array|null
	 * 
	 * @todo пересмотреть!!!
	 */
	static public function getNodeData($node_id)
	{
		return array_key_exists($node_id, self::$nodes_list_static) ? self::$nodes_list_static[$node_id] : null;
	}
	
	/**
	 * Получить список нод, участвующих в запросе.
	 *
	 * @param int $node_id
	 * @return array|false
	 * 
	 * @todo сейчас используется только в классе Request, когда требуется создать зависимости кеша от нод. 
	 * 		 думаю во первых можно хранить этот список только если включено кеширование нод, 
	 * 		 а во вторых, лучше в самом объекте Cache_Page, раз только ему это надо.
	 */
	static public function getNodesList()
	{
		return self::$nodes_list_static;
	}
	
	/**
	 * Получить данные результата парсера части УРИ, которые обработала нода.
	 *
	 * @return array
	 * 
	 * @todo убрать куда-нить!!!
	 */
	static public function getParserNodeData()
	{
		return self::$parser_node_id;
	}
	
	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		if (parent::$profiler_enable !== false) {
			echo "\n<pre>\n" . round(microtime(true) - START_TIME, 4) . " сек, \n";
			echo cf_format_filesize(memory_get_peak_usage(true)) . " (" . memory_get_peak_usage(true) . " байт) памяти, \n";
			echo DB::getQueryCount() . " запросов в БД\n</pre>\n";			
		}
//		cmf_dump(DB::$query_log, 'DB::$query_log');
//		cmf_dump(DB::$query_log_profiler, 'DB::$query_log_profiler');
//		cmf_dump(DB::getStat(), 'DB::getStat()');
//		cmf_dump(DB::getQueryesDublicates(), 'getQueryesDublicates:');
		
//		cmf_dump(Profiler::getResult());
	
//		unset($this->EE->admin);
//		cmf_dump($this->EE);		
//		cmf_dump($this->EE->template, 'Массив template');
//		cmf_dump($this->EE->admin['toolbar']);
//		cmf_dump($this->Env);
//		cmf_dump($this->Session);
//		cmf_dump($this->Cookie);
	}	
}