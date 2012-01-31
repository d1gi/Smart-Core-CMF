<?php 
/**
 * Класс по работе с Папками.
 * 
 * @author	Artem Ryzhkov
 * @package	Kernel
 *          
 * @uses	Breadcrumbs
 * @uses	DB
 * @uses	Env
 * @uses	View
 * @uses	User
 * 
 * @version 2012-01-24.0
 */
class Admin_Folder extends Folder
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->View->setTpl('folder');
	}
	
	/**
	 * run
	 *
	 * @param string $uri_path 
	 * @return array
	 */
	public function run($uri_path)
	{
		if (count($_POST) > 0) {
			$this->postProcessor();
		}
		
		$this->setIsActive('all');
		
		$uri_path_parts = explode('/', $uri_path);
		
		// Редактирование папки.
		if (is_numeric($uri_path_parts[0])) {
			$this->Breadcrumbs->add($uri_path_parts[0] . '/', 'Редактирование папки id: ' . $uri_path_parts[0]);
			$folder = $this->getDataById($uri_path_parts[0]);
			
			if ($folder === false) {
				return false;
			}
			
			// Редактирование мета-данных папки.
			if (isset($uri_path_parts[1]) and $uri_path_parts[1] === 'meta') {
				$this->Breadcrumbs->add('meta/', 'Мета-данные');
				$Meta = new Component_Meta(unserialize($folder->meta));
				$this->View->meta_controls = $Meta->getControls(array('folder_id' => $uri_path_parts[0]));
			} else {
				$Node = new Node();
				$this->View->meta  = unserialize($folder->meta);
				$this->View->nodes = $Node->getListInFolder($uri_path_parts[0]);
				$this->View->form  = $this->getEditFormData($uri_path_parts[0]);
			}
		}
		// Создание папки
		else if ($uri_path_parts[0] == 'create') {
			$this->Breadcrumbs->add('create/', 'Создание новой папки');
			if (!isset($uri_path_parts[1]) or !is_numeric($uri_path_parts[1])) {
				$uri_path_parts[1] = 1;
			}
			$this->View->form = $this->getCreateFormData($uri_path_parts[1]);
		}
		// Список всех папок
		else {
			$this->View->all = $this->getList();
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
		
		if (isset($_POST['folder_id'])) {
			$folder_id = $_POST['folder_id'];
		}
		
		switch ($submit) {
			case 'cancel':
				break;
			case 'create_meta':
				$this->createMeta($folder_id, $_POST['pd']);
				cmf_redirect();
				break;
			case 'update_meta_tags':
				$this->updateMeta($folder_id, $_POST['pd']);
				cmf_redirect();
				break;
			case 'save':
				$this->update($folder_id, $_POST['pd']);
				break;
			case 'new':
				$folder_id = $this->create($_POST['pd']);
				break;
			default;
		}

		if ($_POST['target'] == '_parent') {
			if ($_POST['pd']['is_active']) {
				cmf_redirect(parent::getUri($folder_id));
			} else {
				cmf_redirect(parent::getUri(1));
			}
		} else {
			cmf_redirect($this->Breadcrumbs->getLastUri());
		}		
	}
	
	/**
	 * Получение данных для формирования формы создания новой папки.
	 *
	 * @param int $folder_id
	 * @return array
	 */
	public function getCreateFormData($folder_id = false)
	{
		if ($folder_id === false or !is_numeric($folder_id)) {
			$folder_id = $this->Env->current_folder_id;
		}
		
		$sql = "SELECT max(pos) AS max_pos
			FROM {$this->DB->prefix()}engine_folders
			WHERE pid = '{$folder_id}'
			AND site_id = '{$this->Env->site_id}' ";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		
		$parser_node_id_options = array(0 => '-');
		$sql2 = "SELECT * 
			FROM {$this->DB->prefix()}engine_nodes 
			WHERE '{$this->Env->site_id}'
			AND folder_id = '$folder_id'
			AND is_active = 1 ";
		$result2 = $this->DB->query($sql2);
		while ($row2 = $result2->fetchObject()) {
			$parser_node_id_options[$row2->node_id] = "[$row2->node_id] - $row2->descr ($row2->module_id) ";
		}
						
		$target = isset($_GET['popup']) ? '_parent' : '_self';

		return array(
			'target' => $target,
			'hiddens' => array( 
				'folder_pid' => $folder_id,
				'target' => $target,
				),
			'elements' => array(
				'pd[is_active]' => array(
					'label' => 'Включено',
					'type' => 'checkbox',
					'value' => 1,
					),
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'string',
					'value' => '',
					),
				'pd[uri_part]' => array(
					'label' => 'Часть URI',
					'type' => 'string',
					'value' => '',
					),
				'pd[descr]' => array(
					'label' => 'Описание',
					'type' => 'string',
					'value' => '',
					),
				'pd[pos]' => array(
					'label' => 'Позиция',
					'type' => 'string',
					'value' => $row->max_pos + 1,
					),
				'pd[pid]' => array(
					'label' => 'Родительская папка',
					'type' => 'select',
					'value' => $folder_id,
					'options' => $this->getSelectOptionsArray(),
					),
				'pd[redirect_to]' => array(
					'label' => 'Редирект',
					'type' => 'string',
					'value' => '',
					),
				'pd[is_file]' => array(
					'label' => 'Является файлом',
					'type' => 'checkbox',
					'value' => 0,
					),
				'pd[transmit_nodes]' => array(
					'label' => 'Наследование нод',
					'type' => 'checkbox',
					'value' => 0,
					),
				'pd[parser_node_id]' => array(
					'label' => 'Парсер нода ID',
					'type' => 'select',
					'value' => '',
					'options' => $parser_node_id_options,
					),
				'pd[permissions]' => array(
					'label' => 'Права доступа',
					'type' => 'string',
//					'value' => '0|read:0', // По умолчанию запрещено читать для гостей.					
					'value' => '',
					),
				'pd[layout]' => array(
					'label' => 'Макет',
					'type' => 'string',
					'value' => '',
					),
				),
			'buttons' => array(
				'submit[new]' => array(
					'value' => 'Создать папку',
					'type' => 'submit',
					),
				'submit[cancel]' => array(
					'value' => 'Отменить',
					'type' => 'submit',
					),
				),
			'autofocus' => 'pd[title]',
			'help' => 'Cправка по редактированию ноды'
			);
	}
	
	/**
	 * Получение данных для формирования формы редактирования папки.
	 *
	 * @param int $folder_id
	 * @return array
	 */
	public function getEditFormData($folder_id = false)
	{
		if ($folder_id === false ) {
			$folder_id = $this->Env->current_folder_id;
		}
		
		$folder = $this->getDataById($folder_id);
		
		$parser_node_id_options = array(0 => '-');
		$sql2 = "SELECT node_id, descr, module_id 
			FROM {$this->DB->prefix()}engine_nodes 
			WHERE '{$this->Env->site_id}'
			AND folder_id = '$folder->folder_id'
			AND is_active = 1 ";
		$result2 = $this->DB->query($sql2);
		while ($row2 = $result2->fetchObject()) {
			$parser_node_id_options[$row2->node_id] = "[$row2->node_id] - $row2->descr ($row2->module_id) ";
		}
		
		$nodes_blocks = array(
			'single'  => '', // Блокировка нод в папке, без наследования.
			'inherit' => '', // Блокировка нод в папке, с наследованием.
			'except'  => '', // Блокировка всех нод в папке, кроме заданных.
			);
		
		if (!empty($folder->nodes_blocks)) {
			$tmp = unserialize($folder->nodes_blocks);
			if (isset($tmp['single'])) {
				$nodes_blocks['single'] = $tmp['single'];
			}
			if (isset($tmp['inherit'])) {
				$nodes_blocks['inherit'] = $tmp['inherit'];
			}
			if (isset($tmp['except'])) {
				$nodes_blocks['except'] = $tmp['except'];
			}
			unset($tmp);
		}
		
		$target = isset($_GET['popup']) ? '_parent' : '_self';
		
		$user_data = $this->User->getData($folder->owner_id);
		return array(
			'target' => $target,
			'hiddens' => array( 
				'folder_id' => $folder->folder_id,
				'target' => $target,
				),
			'elements' => array(
				'pd[is_active]' => array(
					'label' => 'Включено',
					'type' => 'checkbox',
					'value' => $folder->is_active,
					),
				'_create_datetime' => array(
					'label' => 'Папка создана',
					'type' => 'html',
					'value' => $folder->create_datetime . " (<a href=\"" . HTTP_ROOT . ADMIN . "/users/edit/" . $user_data['user_id'] . "/\">" . $user_data['nickname'] . "</a>)",
					),
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'string',
					'value' => $folder->title,
					),
				'pd[uri_part]' => array(
					'label' => 'Часть URI',
					'type' => 'string',
					'value' => $folder->uri_part,
					'readonly' => $folder->pid == 0 ? true : false, // Деактивировать редактирование элемента uri_part, для корневой папки.
					),
				'pd[descr]' => array(
					'label' => 'Описание',
					'type' => 'string',
					'value' => $folder->descr,
					),
				'pd[pos]' => array(
					'label' => 'Позиция',
					'type' => 'string',
					'value' => $folder->pos,
					),
				'pd[pid]' => array(
					'label' => 'Родительская папка',
					'type' => 'select',
					'value' => $folder->pid,
					'options' => $folder->pid == 0 ? array(0 => '-') : $this->getSelectOptionsArray($folder->folder_id),
					),
				'pd[redirect_to]' => array(
					'label' => 'Редирект',
					'type' => 'string',
					'value' => $folder->redirect_to,
					),
				'pd[is_file]' => array(
					'label' => 'Является файлом',
					'type' => 'checkbox',
					'value' => $folder->is_file,
					),
				'pd[transmit_nodes]' => array(
					'label' => 'Наследование нод',
					'type' => 'checkbox',
					'value' => $folder->transmit_nodes,
					),
				'pd[parser_node_id]' => array(
					'label' => 'Парсер нода ID',
					'type' => 'select',
					'value' => $folder->parser_node_id,
					'options' => $parser_node_id_options,
					),
				'pd[permissions]' => array(
					'label' => 'Права доступа',
					'type' => 'string',
					'value' => $folder->permissions,
					),
				'pd[layout]' => array(
					'label' => 'Макет',
					'type' => 'string',
					'value' => $folder->layout,
					),
				'pd[nodes_blocks][single]' => array(
					'label' => 'Блокировка нод single',
					'type' => 'string',
					'value' => $nodes_blocks['single'],
					),
				'pd[nodes_blocks][inherit]' => array(
					'label' => 'Блокировка нод inherit',
					'type' => 'string',
					'value' => $nodes_blocks['inherit'],
					),
				'pd[nodes_blocks][except]' => array(
					'label' => 'Блокировка нод except',
					'type' => 'string',
					'value' => $nodes_blocks['except'],
					),
				),
			'buttons' => array(
				'submit[save]' => array(
					'value' => 'Сохранить изменения',
					'type' => 'submit',
					),
				'submit[cancel]' => array(
					'value' => 'Отменить',
					'type' => 'submit',
					),
				),
			'autofocus' => 'pd[title]',
			'help' => 'Cправка по редактированию папки'
			);
	}
}