<?php
/**
 * Управляющий котроллер административной панели.
 * 
 * @author	Artem Ryzhkov
 * @package	Kernel
 * @link	http://smart-core.org/
 * @license	http://opensource.org/licenses/gpl-2.0
 * 
 * @uses 	Permissions
 * @uses 	User
 * @uses 	View
 * @uses 	Zend_Config_Yaml
 * 
 * @version 2012-01-24.0
 */
class Admin extends Controller
{
	/**
	 * Структура административной панели.
	 * @var array
	 */
	protected $structure;
	
	/**
	 * 
	 */
	protected $current_section = false;
	
	/**
	 * Путь к YAML-файлу - конфигу админской структуры.
	 * @var string
	 */
	protected $config_yaml_file;
	
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
		
		$this->config_yaml_file	= DIR_CORE . 'Admin/admin.yml';
	}
	
	/**
	 * Получить структуру административной панели в виде массива.
	 * Данные берутся из файла admin.yml
	 *
	 * @return array
	 * 
	 * @todo кешировать результат структуры админки т.к. сейчас он декодируется примерно за 0.15 сек.
	 */
	protected function getStructure()
	{
		return Zend_Config_Yaml::decode(file_get_contents($this->config_yaml_file));
	}

	/**
	 * Парсер запроса.
	 *
	 * @param string $path
	 * @return string - Обработанный фрагмент УРИ т.е. то начальная его часть.
	 */
	public function router($path)
	{
		$controller			= false;
		$action				= 'run';
		$breadcrumbs		= array();		
		$path_parts			= explode('/', $path);
		$controller_path	= $path;
		$admin_path			= HTTP_ROOT . ADMIN . '/';
		
		if ($this->Permissions->isRoot()) {
			$this->current_section = $this->getStructure();
			
			foreach ($path_parts as $key) {
				if (isset($this->current_section[$key])) {
					$admin_path .= $key . '/';
					$controller_path = str_replace($key . '/', '', $controller_path); // @todo сделать более правильно ;)
					
					if (isset($this->current_section[$key]['class'])) {
						$controller = $this->current_section[$key]['class'];
					}
					
					$breadcrumbs[] = array(
						'uri'	=> $key . '/', 
						'title'	=> $this->current_section[$key]['title'],
						'descr'	=> isset($this->current_section[$key]['descr']) ? $this->current_section[$key]['descr'] : false
						);
					$this->current_section = isset($this->current_section[$key]['_items_']) ? $this->current_section[$key]['_items_'] : false;
				} else {
					break;
				}
			}
		}
		
		return array(
			'controller'		=> $controller,
			'action'			=> $action,
			'controller_path'	=> $controller_path,
			'admin_path'		=> $admin_path,
			'current_section'	=> $this->current_section,
			'breadcrumbs'		=> $breadcrumbs,
			);
	}
	
	/**
	 * Получить форму аторизации.
	 * 
	 * @return array
	 */
	public function getLoginFormData()
	{
		return array(
			'class' => 'login-form',
			'elements' => array(
				'pd[login]' => array(
					'label' => 'Логин',
					'type' => 'text',
					),
				'pd[pass]' => array(
					'label' => 'Пароль',
					'type' => 'password',
					),
				'pd[remember]' => array(
					'label' => 'Запомнить',
					'type' => 'select',
					'value' => 0,
					'options' => array(
						'0' => 'Не запоминать',
						'30' => '30 минут',
						'60' => '60 минут',
						'1d' => '1 день',
						'7d' => 'Неделя',
						'1m' => 'Месяц',
						'forever' => 'Навсегда',
						),
					),
				),
			'buttons' => array(
				'submit[auth]' => array(
					'value' => 'Войти',
					'type' => 'submit',
					),
				),
			'help' => 'Cправка по авторизации'
			);
	}
	
	/**
	 * Страница авторизации.
	 */
	public function loginPage()
	{
		$this->View->setTpl('login');

		$tmp = $this->Site->getProperties();
		$this->View->welcome_message = "<h1>{$tmp['full_name']}</h1>Вход в административную панель.<br />Здесь может авторизоваться только администратор.";
		$this->View->homepage		 = $this->Breadcrumbs->get(0);
		$this->View->login_form		 = $this->getLoginFormData();
	}
	
	/**
	 * Выполенение административных действий запрошенных через URI.
	 * 
	 * @access public
	 * @param string $params
	 */
	public function run($params)
	{
		switch ($_SERVER['REQUEST_METHOD']) {
			case 'GET':
				foreach ($params['folders'] as $folder) {
					$this->Breadcrumbs->add($folder['uri'], $folder['title'], $folder['descr']);
				}	
				$this->Breadcrumbs->add(ADMIN . '/', 'Управление', 'Администрирование системой.');

				foreach ($params['breadcrumbs'] as $bc) {
					$this->Breadcrumbs->add($bc['uri'], $bc['title'], $bc['descr']);
				}	

				$this->ScriptsLib->request('jquery');
				$this->Html->addHeadScript('backend.js', HTTP_SYS_RESOURCES . 'admin/backend/backend.js'); // @todo переделать!
				$this->Html->addDocumentReady('fieldsetsToTabs($j);'); // @todo переделать!
				
				// У пользователя есть права доступа.
				if ($this->Permissions->isRoot()) {
					$this->View->Breadcrumbs = $this->Breadcrumbs;
					
					if ($params['controller']) {
						$Controller = new $params['controller'];
						$Controller->run($params['controller_path']);
						
						if (!empty($Controller->View->menu)) {
							$this->View->Navigation = new View;
							$this->View->Navigation->menu = $Controller->View->menu;
							foreach ($this->View->Navigation->menu as $key => $value) {
								$this->View->Navigation->menu[$key]['uri'] = $params['admin_path'] . $value['uri'];
								if (isset($value['selected']) and $value['selected'] == true and !empty($value['uri'])) {
									$this->Breadcrumbs->add($value['uri'], $value['title'], $value['descr']);
								}
							}
							$this->View->Navigation->setTpl('nav');
						}
						$this->View->Content = $Controller->View;
					} else {
						// Если запрошена главная страница админки, то подготавливается приветствие и кнопка выхода.
						if (count($this->Breadcrumbs->get()) == 2) {
							$this->View->welcome = $this->User->getData();
						}
						$this->View->current_menu = $params['current_section'];
					}			
				} else {
					// Пользователь не обладает правами доступа, ему предлагается авторизоваться.
					$this->loginPage();
				}
				
				$this->Html->addHeadStyle('system_admin.css', HTTP_SYS_RESOURCES . 'admin/backend/system_admin.css'); // @todo переделать!
				
				break;
			case 'POST':
				//cmf_dump($_POST);cmf_dump($params);exit;
				if (empty($params['controller'])) {
					$this->postProcessor();
				} else{
					$Controller = new $params['controller'];
					$Controller->$params['action']($params['controller_path']);
				}
				cmf_redirect();
				break;
			default;
		}
		
	}
	
	/**
	 * Обработчик POST данных.
	 */
	protected function postProcessor()
	{
		foreach ($_POST['submit'] as $key => $value) {
			$submit = $key;
		}

		switch ($submit) {
			case 'auth':
				$pd = $_POST['pd'];
				if (isset($pd['login']) and isset($pd['pass'])) {
					if ($this->User->login($pd['login'], $pd['pass'])) {
						$this->Permissions->rebuild();
						if (!$this->Permissions->isRoot()) {
							cmf_redirect(HTTP_ROOT);
						}
					}
				}
				break;
			case 'user_logout':
				$this->User->logout();
				cmf_redirect(HTTP_ROOT);
				break;
			default;
		}
	}
}
