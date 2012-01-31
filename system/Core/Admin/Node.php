<?php
/**
 * Класс администрирования Нод.
 * 
 * @author	Artem Ryzhkov
 * @package	Kernel
 * 
 * @uses	Breadcrumbs
 * @uses	Block
 * @uses	DB
 * @uses	Folder
 * @uses	Kernel
 * @uses	User
 * 
 * @version 2012-01-24.0
 */
class Admin_Node extends Node
{	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->View->setTpl('node');
	}

	/**
	 * Получить данные формы редактирования ноды.
	 *
	 * @param int $folder_id
	 * @return array - массив с данными для формирования формы.
	 * 
	 * @todo выбор Макета
	 */
	public function getEditFormData($node_id)
	{
		$Module = $this->getModuleInstance($node_id, true);
		if (!is_object($Module)) {
			return null;
		}

		$params = $Module->getParams();
		
		$sql = "SELECT * FROM {$this->DB->prefix()}engine_nodes WHERE node_id = '$node_id' AND site_id = '{$this->Env->site_id}' ";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		
		$Block	= new Block();
		$Folder	= new Folder();
		$target	= isset($_GET['popup']) ? '_parent' : '_self';
		
		$user_data = $this->User->getData($row->owner_id);
		$form_data = array(
			'target' => $target,
			'hiddens' => array( 
				'node_id' => $node_id,
				'target' => $target,
				),
			'elements' => array(
				'pd[is_active]' => array(
					'label' => 'Включено',
					'type' => 'checkbox',
					'value' => $row->is_active,
					),
				'pd[is_cached]' => array(
					'label' => 'Кешировать',
					'type' => 'checkbox',
					'value' => $row->is_cached,
					),
				'pd[module_id]' => array(
					'label' => 'Модуль',
					'type' => 'string',
					'value' => $row->module_id,
					'disabled' => true,
					),
				'_create_datetime' => array(
					'label' => 'Нода создана',
					'type' => 'html',
					'value' => $row->create_datetime . " (<a href=\"" . HTTP_ROOT . ADMIN . "/users/edit/" . $user_data['user_id'] . "/\">" . $user_data['nickname'] . "</a>)",
					),
				'pd[descr]' => array(
					'label' => 'Служебное описание',
					'type' => 'string',
					'value' => $row->descr,
					),
				'pd[pos]' => array(
					'label' => 'Позиция',
					'type' => 'string',
					'value' => $row->pos,
					),
				'pd[block_id]' => array(
					'label' => 'Блок',
					'type' => 'select',
					'value' => $row->block_id,
					'options' => $Block->getHtmlSelectOptionsArray(),
					),
				'pd[folder_id]' => array(
					'label' => 'Папка',
					'type' => 'select',
					'value' => $row->folder_id,
					'options' => $Folder->getSelectOptionsArray(),
					),
				'pd[database_id]' => array(
					'label' => 'Подключение к БД',
					'type' => 'select',
					'value' => $row->database_id,
					'options' => $this->DB_Resources->getListOptions(),
					),
				'pd[permissions]' => array(
					'label' => 'Права доступа',
					'type' => 'string',
					'value' => $row->permissions,
					),
				'pd[plugins]' => array(
					'label' => 'Плагины',
					'type' => 'string',
					'value' => $row->plugins,
					),
				'pd[cache_params_yaml]' => array(
					'label' => 'Параметры кеширования',
					'type' => 'textarea',
					'value' => $row->cache_params_yaml,
					),
				),
			'autofocus' => 'pd[descr]',
			'buttons' => array(
				'submit[update]' => array(
					'value' => 'Сохранить изменения',
					'type' => 'submit',
					),
				'submit[cancel]' => array(
					'value' => 'Отменить',
					'type' => 'submit',
					),
				),
			'help' => 'Cправка по редактированию ноды'
			);
		// Параметры подключения модуля.
		$module_params = array();
		if (count($params) > 0) {
			foreach ($params as $key => $value) {
				foreach ($value as $key2 => $value2) {
					$form_data['elements']["pd[params][$key]"][$key2] = $value2;
				}
				$module_params[] = "pd[params][$key]";
			}
		}
		// Филдсеты.
		$form_data['fieldsets'] = array(
			'module_params' => array(
				'title' => 'Параметры подключения модуля',
				'elements' => $module_params,
			),
			'node_properties' => array(
				'title' => 'Основные свойства ноды',
				'elements' => array(
					'pd[is_active]',
					'pd[is_cached]',
					'pd[module_id]',
					'_create_datetime',
					'pd[descr]',
					'pd[pos]',
					'pd[block_id]',
					'pd[folder_id]',
					'pd[database_id]',
					'pd[permissions]',
					'pd[plugins]',
					'pd[cache_params_yaml]',
				),
			),
			/*
			'permissions' => array(
				'title' => 'Права доступа',
				'elements' => array('pd[permissions]'),
				),
			*/
			);
	
		if (count($this->DB_Resources->getListOptions()) == 1) {
			unset($form_data['elements']['pd[database_id]']);
			unset($form_data['fieldsets']['node_properties']['elements'][8]);
		}
		
		return $form_data;
	}
		
	/**
	 * Форма создания новой ноды.
	 * 
	 * @param int $folder_id
	 * @return array - массив с данными для формирования формы.
	 */
	public function getCreateFormData($folder_id)
	{
		if ($folder_id === false ) {
			$folder_id = $this->Env->current_folder_id;
		}
		
		// Определение максимальной позиции в текущей папке.		
		$sql = "SELECT max(pos) AS max_pos
			FROM {$this->DB->prefix()}engine_nodes
			WHERE folder_id = '{$folder_id}'
			AND site_id = '{$this->Env->site_id}' ";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		$pos = $row->max_pos + 1;
		
		$Block = new Block();
		$Folder = new Folder();

		$data = array();
		$sql = "SELECT * FROM {$this->DB->prefix()}engine_modules "; // @todo site_id
		$result = $this->DB->query($sql);
		while($row = $result->fetchObject()) {
			$data[$row->module_id] = $row->descr . ' (' . $row->module_id . ')';
		}

		$target = isset($_GET['popup']) ? '_parent' : '_self';
		
		$form_data = array(
			'target' => $target,
			'hiddens' => array( 
				'action' => 'new_node',
				'target' => $target,
				),
			'elements' => array(
				'pd[is_active]' => array(
					'label' => 'Включено',
					'type' => 'checkbox',
					'value' => 1,
					),
				'pd[is_cached]' => array(
					'label' => 'Кешировать',
					'type' => 'checkbox',
					'value' => 1,
					),
				'pd[module_id]' => array(
					'label' => 'Модуль',
					'type' => 'select',
					'value' => 'Texter',
					'options' => $data,
					),
				'pd[descr]' => array(
					'label' => 'Служебное описание',
					'type' => 'string',
					'value' => '',
					),
				'pd[pos]' => array(
					'label' => 'Позиция',
					'type' => 'string',
					'value' => $pos,
					),
				'pd[block_id]' => array(
					'label' => 'Блок',
					'type' => 'select',
					'value' => 1,
					'options' => $Block->getHtmlSelectOptionsArray(),
					),
				'pd[folder_id]' => array(
					'label' => 'Папка',
					'type' => 'select',
					'value' => $folder_id,
					'options' => $Folder->getSelectOptionsArray(),
					),
				'pd[database_id]' => array(
					'label' => 'Подключение к БД',
					'type' => 'select',
					'value' => 0,
					'options' => $this->DB_Resources->getListOptions(),
					),
				'pd[permissions]' => array(
					'label' => 'Права доступа',
					'type' => 'string',
					'value' => '',
					),
				),
			'autofocus' => 'pd[descr]',
			'buttons' => array(
				'submit[create]' => array(
					'value' => 'Добавить ноду',
					'type' => 'submit',
					),
				'submit[cancel]' => array(
					'value' => 'Отменить',
					'type' => 'submit',
					),
				),
			'help' => 'Cправка по добавлению ноды.'
			);

		if (count($this->DB_Resources->getListOptions()) == 1) {
			unset($form_data['elements']['pd[database_id]']);
		}
			
		return $form_data;
	} 
	
	/**
	 * run
	 *
	 * @param 
	 * @return
	 */
	public function run($uri_path)
	{
		if (count($_POST) > 0) {
			$this->postProcessor();
			return;
		}
		
		$uri_path_parts = explode('/', $uri_path);
		
		// Редактирование ноды.
		if (is_numeric($uri_path_parts[0])) {
			$this->Breadcrumbs->add($uri_path_parts[0] . '/', 'Редактирование параметров Ноды id: ' . $uri_path_parts[0]);
			$this->View->form = $this->getEditFormData($uri_path_parts[0]);			
		}
		// Создание ноды
		else if ($uri_path_parts[0] == 'create') {
			$this->Breadcrumbs->add('create/', 'Создание новой ноды');
			if (!isset($uri_path_parts[1]) or !is_numeric($uri_path_parts[1])) {
				$uri_path_parts[1] = 1;
			}
			$this->View->form = $this->getCreateFormData($uri_path_parts[1]);
		}
		else if ($uri_path_parts[0] == 'in_folder') {
			$this->Breadcrumbs->add('in_folder/', 'Все ноды в папке id: ' . $uri_path_parts[1]);
			$this->View->list = $this->getListInFolder($uri_path_parts[1]);
			$this->View->current_folder = 'http://' . HTTP_HOST . Folder::getUri($uri_path_parts[1]);
		}
		// Список всех нод
		else {
			$this->View->list = $this->getListInFolder();
		}
	}

	/**
	 * Обработчик POST данных.
	 */
	public function postProcessor()
	{
		foreach ($_POST['submit'] as $key => $value) {
			$submit = $key;
		}
		
		$folder_id = $_POST['pd']['folder_id'];
		switch ($submit) {
			case 'cancel':
				break;
			case 'update':
				$this->update($_POST['node_id'], $_POST['pd']);
				break;
			case 'create':
				$this->create($_POST['pd']);
				break;
			default;
		}

		if ($_POST['target'] == '_parent') {
			cmf_redirect(Folder::getUri($folder_id));
		} else {
			cmf_redirect('../');
		}
	}
}