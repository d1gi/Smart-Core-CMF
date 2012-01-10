<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Обработка Admin запросов.
 * 
 * @author	Artem Ryzhkov
 * @package	Kernel
 * @link	http://smart-core.org/
 * @license	http://opensource.org/licenses/gpl-2.0
 * 
 * @uses 	EE
 * @uses 	Node
 * @uses 	Permissions
 * @uses 	User
 * @uses 	Zend_Config_Yaml
 * 
 * @version 2011-12-25.0
 */
class Admin extends Base
{
	protected $front_end_action_mode = false;
	protected $front_end_action_node_id = false;
	
	/**
	 * Структура административной панели.
	 * @var array
	 */
	protected $structure;
	
	/**
	 * 
	 */
	protected $current_section;
	
	/**
	 * Класс, который надо выполнить.
	 */
	protected $exec_class = false;
	protected $exec_class_params = false;
	
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

		$this->setTpl('admin');
		$this->setTplPath(DIR_KERNEL . 'Admin/');
		
		$this->config_yaml_file				= DIR_KERNEL . 'Admin/admin.yml';
		$this->structure 					= $this->getStructure();
		$this->EE->breadcrumbs[0]['descr']	= 'Перейти на сайт: ' . $this->EE->head['site_full_name'];
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
		/*
		$t = microtime(true);
		$tmp = Zend_Config_Yaml::decode(file_get_contents($this->config_yaml_file));
		cf_debug(microtime(true) - $t);
		return $tmp;
		*/
		return Zend_Config_Yaml::decode(file_get_contents($this->config_yaml_file));
	}

	/**
	 * Парсер запроса.
	 *
	 * @param string $uri_path
	 * @return string - Обработанный фрагмент УРИ т.е. то начальная его часть.
	 */
	public function parser($uri_path)
	{
		$uri_path_parts = explode('/', $uri_path);
		$used_uri = '';
		$this->current_section = $this->structure;

		$breadcrumbs = HTTP_ROOT . ADMIN . '/';
		
		foreach ($uri_path_parts as $key) {
			if (isset($this->current_section[$key])) {
				
				$used_uri .= $key . '/';
				
				if (isset($this->current_section[$key]['class'])) {
					$this->exec_class = $this->current_section[$key]['class'];
				}
				
				$descr = isset($this->current_section[$key]['descr']) ? $this->current_section[$key]['descr'] : false;
				$breadcrumbs .=  $key . '/';
				$this->EE->addBreadCrumb($breadcrumbs, $this->current_section[$key]['title'], $descr);
				$this->current_section = isset($this->current_section[$key]['_items_']) ? $this->current_section[$key]['_items_'] : false;
			} else {
				break;
			}
		}

		return $used_uri;
	}
	
	/**
	 * Выполенение административных действий запрошенных через URI.
	 * 
	 * @access public
	 * 
	 * @param string $uri_part - часть запроса, следующая за папкой указывающей на действие.
	 * @return bool - успешность выполнения действия.
	 */
	public function run($uri_path)
	{
		$data = array();
		$module_id = 'Admin';
		$this->EE->template['dir_theme'] = DIR_KERNEL . 'Admin/';
		$this->EE->template['theme_name'] = ''; // @todo можно будет сделать смену тем для админки.
		$this->EE->addBreadCrumb(HTTP_ROOT . ADMIN . '/', 'Управление', 'Администрирование системой.');
		$this->EE->addHeadScript('backend.js', HTTP_SYS_RESOURCES . 'admin/backend/backend.js');
		$this->EE->addDocumentReady('fieldsetsToTabs($j);');
		
		// Пользователь не обладает правами доступа, ему предлагается авторизоваться.
		if (!$this->Permissions->isRoot()) {
			$data['login_form'] = array(
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
			$data['welcome_message'] = "<h1>{$this->EE->head['site_full_name']}</h1>Вход в административную панель.<br />Здесь может авторизоваться только администратор.";
			$this->EE->template['layout'] = 'blank';
			$this->setTpl(DIR_KERNEL . 'Admin/login.tpl');
		} 
		// У пользователя есть права доступа.
		else {
			$this->EE->template['layout'] = 'admin';

			$used_uri = $this->parser($uri_path);
			
			$data['current_menu'] = $this->current_section;
			
			if ($this->exec_class == 'Admin_Module' or $this->exec_class == 'Admin_Component') {
				$AdminClass	= new $this->exec_class();
				$AdminClass->action(substr($uri_path, strlen($used_uri)));
				$data 		= $AdminClass->getOutputData();
				$module_id	= $AdminClass->getModuleName();
				$this->setTpl($AdminClass->getTpl());
				$this->setTplPath($AdminClass->getTplPath());
			} else if (!empty($this->exec_class)) {
				$AdminClass	= new $this->exec_class();
				$AdminClass->action(substr($uri_path, strlen($used_uri)));
				$data = $AdminClass->getOutputData();
				$this->setTpl($AdminClass->getTpl());				
			}
		}

		// Если запрошена главная страница админки, то подготавливается приветствие и кнопка выхода.
		if (count($this->EE->breadcrumbs) == 2) {
			if (count($_POST) > 0) {
				$this->postProcessor();
			}
			
			$data['welcome'] = $this->User->getData();
		}
		
		// @todo продумать админские шаблоны... сейчас пока применяется контейнер 'content' и в ноду №1 складываются данные.
		$this->EE->data['content'][1] = array( 
			'node_action_mode'	=> 'popup',
			'tpl'				=> $this->getTpl(),
			'tpl_path' 			=> $this->getTplPath(),
			'data'				=> $data,
			);
			
		// Хлебные крошки. В данном случа форсированно используется шаблон модуля Breadcrumbs.
		$this->EE->data['breadcrumbs'][1] = array(
			'node_action_mode'	=> 'popup',
			'tpl'				=> 'Breadcrumbs',
			'tpl_path'			=> DIR_MODULES . 'Breadcrumbs/',
			'data'				=> array(
				'delimiter' => '&raquo;',
				'items' 	=> $this->EE->breadcrumbs,
				),
			);
		$this->EE->addHeadStyle('system_admin.css', HTTP_SYS_RESOURCES . 'admin/backend/system_admin.css');
		$this->front_end_action_mode = 'popup';
		return true;
	}
	
	/**
	 * Обработчик POST данных.
	 *
	 * @param
	 * @return
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
							cf_redirect(HTTP_ROOT);
						}
					}
				}
				break;
			case 'user_logout':
				$this->User->logout();
				cf_redirect(HTTP_ROOT);
				break;
			default;
		}
	}
	
	/**
	 * NewFunction
	 *
	 * @param
	 * @return string
	 */
	public function getFrontEndActionMode()
	{
		return $this->front_end_action_mode;
	}
	
	/**
	 * NewFunction
	 *
	 * @param
	 * @return string
	 */
	public function getFrontEndActionNodeId()
	{
		return $this->front_end_action_node_id;
	}
	
}