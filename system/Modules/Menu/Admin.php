<?php 
/**
 * Класс с административными методами.
 * 
 * @version 2012-01-24.0
 */
class Module_Menu_Admin extends Module_Menu implements Admin_ModuleInterface
{
	/**
	 * NewFunction
	 *
	 * @param
	 */
	public function admin($uri_path)
	{
		// Обработчик POST данных.
		if (isset($_POST['action']) and !isset($_POST['submit']['cancel'])) {
			switch ($_POST['action']) {
				case 'create_group':
					$this->createGroup($_POST['pd']);
					break;
				default;
			}
		}
		
		if (isset($_GET['del_group']) and is_numeric($_GET['del_group'])) {
			$this->deleteGroup($_GET['del_group']);
			cmf_redirect(HTTP_ROOT . ADMIN . '/module/Menu/');
		}

		$this->View->setTpl(DIR_MODULES . 'Menu/Admin.tpl');
		
//		$uri_path_parts = explode('/', $uri_path);
		
		$result = $this->DB->query("SHOW TABLES LIKE '{$this->DB->prefix()}menu_groups' ");
		if ($result->rowCount() == 0) {
			return false;
		}
		
		$this->View->groups_list = $this->getGroupsList();
		
//		if (isset($uri_path_parts[0]) and is_numeric($uri_path_parts[0])) {
			$form_data = array(
				'action' => HTTP_ROOT . ADMIN . '/module/Menu/',
				'hiddens' => array(
					'action' => 'create_group',
					),
				'elements' => array(
					'pd[descr]' => array(
						'label' => 'Описание',
						'type' => 'text',
						'value' => '',
						),
					'pd[name]' => array(
						'label' => 'Служебное имя',
						'type' => 'text',
						'value' => '',
						),
					'pd[pos]' => array(
						'label' => 'Позиция',
						'type' => 'text',
						'value' => 0,
						),
					),
				'autofocus' => 'pd[descr]',
				'buttons' => array(
					'submit[save]' => array(
						'type' => 'submit',
						'value' => 'Создать группу',
						),
					),
				);
			$this->View->create_group_form_data = $form_data;
//		}
	}
	
	/**
	 * Создание новой группы.
	 *
	 * @param array $pd
	 * @return int|false
	 */
	public function createGroup($pd)
	{
		$name = $this->DB->quote($pd['name']);
		$descr = $this->DB->quote($pd['descr']);
		$pos = (is_numeric($pd['pos']) and strlen($pd['pos'] < 4)) ? $pd['pos'] : 0;
		
		$sql = "
			INSERT INTO {$this->DB->prefix()}menu_groups
				(site_id, name, descr, pos)
			VALUES
				('{$this->Env->site_id}', $name, $descr, '$pos') ";
		$this->DB->query($sql);
		return $this->DB->lastInsertId();
	}
	
	/**
	 * Удаление группы меню.
	 *
	 * @param int $group_id
	 * @return bool
	 */
	public function deleteGroup($group_id)
	{
		$sql = "DELETE FROM {$this->DB->prefix()}menu_groups
			WHERE site_id = '{$this->Env->site_id}'
			AND group_id = {$this->DB->quote($group_id)} ";
		$this->DB->exec($sql);
		return true;
	}
	
	/**
	 * Получить список групп.
	 *
	 * @param bool $empty_first
	 * @return array
	 */
	public function getGroupsList($empty_first = false)
	{
		$data = array();
		
		if ($empty_first) {
			$data[0] = array('title' => '[Не выбрана]');
		}
		
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}menu_groups
			WHERE site_id = '{$this->Env->site_id}'
			ORDER BY pos ASC ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$sql2 = "SELECT count(item_id) AS cnt
				FROM {$this->DB->prefix()}menu_items
				WHERE site_id = '{$this->Env->site_id}'
				AND group_id = '{$row->group_id}' ";
			$result2 = $this->DB->query($sql2);
			$row2 = $result2->fetchObject();
			
			$data[$row->group_id] = array(
				'title'	=> $row->descr . ' (' . $row2->cnt . ')' , // Для списков select.
				'descr'	=> $row->descr,
				'name'	=> $row->name,
				'pos'	=> $row->pos,
				'items_count' => $row2->cnt,
				);
		}
	
		return $data;
	}
	
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		return array(
			'menu_group_id' => array(
				'label' => 'Группа меню:',
				'type' => 'select',
				'value' => $this->menu_group_id,
				'options' => $this->getGroupsList(true),
				),			
			'max_depth' => array(
				'label' => 'Максимальная вложенность:',
				'type' => 'text',
				'value' => $this->max_depth,
				),
			'css_class' => array(
				'label' => 'CSS класс тега &lt;ul&gt;:',
				'type' => 'text',
				'value' => $this->css_class,
				),
			'selected_inheritance' => array(
				'label' => 'Наследование selected:',
				'type' => 'checkbox',
				'value' => $this->selected_inheritance,
				),
			'tpl' => array(
				'label' => 'Шаблон:',
				'type' => 'string',
				'value' => $this->View->getTpl(),
				),
			);
	}
	
	/**
	 * Получить элементы управления нодой.
	 * 
	 * @return array
	 */
	public function getFrontControls()
	{
		$this->default_action = 'edit';

		$items['edit'] = array(
			'popup_window_title' => 'Редактирование меню',
			'title' => 'Правка',
			'link' => $this->Env->current_folder_path . ACTION . '/' . $this->Node->id . '/',
			'ico' => 'edit',
			);
		return $items;
	}
	
	/**
	 * Функция обработки дейсвий над нодой.
	 * 
	 * @return void
	 */
	public function nodeAction($params)
	{
		$this->View->setTpl('Edit');
		$path_parts = explode('/', $params);
		$link = $this->Node->getUri() . ACTION . '/' . $this->Node->id . '/';
		
		// Выбран пункт меню. Отображается форма его редактирования.
		if (is_numeric($path_parts[0])) {
			$sql = "SELECT * 
				FROM {$this->DB->prefix()}menu_items 
				WHERE item_id = '{$path_parts[0]}'
				AND group_id = '{$this->menu_group_id}' ";
			$result = $this->DB->query($sql);
			$row = $result->fetchObject();

			$Folder = new Folder();
			
			$form_data = array(
				'action' => $link,
				'hiddens' => array(
					'node_id' => $this->Node->id,
					'pd[item_id]' => $row->item_id,
					),
				'elements' => array(
					'pd[is_active]' => array(
						'label' => 'Включен',
						'type' => 'checkbox',
						'value' => $row->is_active,
						),
					'pd[title]' => array(
						'label' => 'Заголовок',
						'type' => 'string',
						'value' => $row->title,
						),
					'pd[pos]' => array(
						'label' => 'Позиция',
						'type' => 'string',
						'value' => $row->pos,
						),
					'pd[pid]' => array(
						'label' => 'Родительский пункт меню',
						'type' => 'string',
						'value' => $row->pid,
						),
					'pd[folder_id]' => array(
						'label' => 'Ссылка на папку',
						'type' => 'select',
						'value' => $row->folder_id,
						'options' => $Folder->getSelectOptionsArray(),
						),
					'pd[direct_link]' => array(
						'label' => 'Прямая ссылка',
						'type' => 'string',
						'value' => $row->direct_link,
						),
					),				
				'autofocus' => 'pd[title]',
				'buttons' => array(
					'submit[save]' => array(
						'type' => 'submit',
						'value' => 'Сохранить изменения',
						),
					'submit[delete]' => array(
						'type' => 'submit',
						'onclick' => "return confirm('Вы уверены, что хотите удалить запись?')",
						'value' => 'Удалить пункт меню',
						),
					'submit[_cancel]' => array(
						'type' => 'submit',
						'value' => 'Отменить',
						),
					),
				);
			$this->View->edit_item_form_data = $form_data;
		}
		// Не выбран никакой пункт меню, отображается древовидный список меню и форма добаления пункта меню.
		else { 
			// @todo сделать права доступа 
			if ($this->Permissions->isRoot() or $this->Permissions->isAdmin()) {
				$this->only_is_active = false;
			}
			
			$Folder = new Folder();
			
			$form_data = array(
				'action' => $link,
				'hiddens' => array(
					'node_id' => $this->Node->id,
					),
				'elements' => array(
					'pd[is_active]' => array(
						'label' => 'Включен',
						'type' => 'checkbox',
						'value' => 1,
						),
					'pd[title]' => array(
						'label' => 'Заголовок',
						'type' => 'string',
						'value' => '',
						),
					'pd[pos]' => array(
						'label' => 'Позиция',
						'type' => 'string',
						'value' => 0,
						),
					'pd[pid]' => array(
						'label' => 'Родительский пункт',
						'type' => 'string',
						'value' => 0,
						),
					'pd[folder_id]' => array(
						'label' => 'Ссылка на папку',
						'type' => 'select',
						'value' => 1,
						'options' => $Folder->getSelectOptionsArray(),
						),
					'pd[direct_link]' => array(
						'label' => 'Прямая ссылка',
						'type' => 'string',
						'value' => '',
						),
					),				
				'autofocus' => 'pd[title]',
				'buttons' => array(
					'submit[create]' => array(
						'type' => 'submit',
						'value' => 'Добавить',
						),
					),
				);
				
			// Собирается массив $this->_folder_tree_list_arr
			$this->_getTreeList($this->getTree(0, 0));
			
			$this->View->list = $this->_folder_tree_list_arr;
			$this->View->new_item_form_data = $form_data;
		}
		$this->View->link = $link;
	}

	/**
	 * Обновление пункта меню.
	 */
	protected function updateItem($pd)
	{
		if (!is_numeric($pd['item_id'])) {
			return false;
		}
		
		$is_active	 = is_numeric($pd['is_active']) ? $pd['is_active'] : 1;
		$pos		 = is_numeric($pd['pos']) ? $pd['pos'] : 0;
		$pid		 = is_numeric($pd['pid']) ? $pd['pid'] : 0;
		$folder_id	 = is_numeric($pd['folder_id']) ? $pd['folder_id'] : 0;
		$direct_link = empty($pd['direct_link']) ? 'NULL' : $this->DB->quote($pd['direct_link']);
		$title		 = strlen($pd['title']) > 0 ? $this->DB->quote($pd['title']) : 'NULL';
		
		$sql = "
			UPDATE {$this->DB->prefix()}menu_items SET
				is_active = '$is_active',
				pid = '$pid',
				folder_id = '$folder_id',
				pos = '$pos',
				direct_link = $direct_link,
				title = $title
			WHERE item_id = '$pd[item_id]'
			AND site_id = '{$this->Env->site_id}' ";
		$this->DB->exec($sql);
	}
	
	/**
	 * Удаление пункта меню.
	 */
	protected function deleteItem($pd)
	{
		return is_numeric($pd['item_id'])
			? $this->DB->exec("DELETE FROM {$this->DB->prefix()}menu_items WHERE item_id = '$pd[item_id]' AND site_id = '{$this->Env->site_id}' ")
			: false;
		/*		
		if (is_numeric($pd['item_id'])) {
			return $this->DB->exec("DELETE FROM {$this->DB->prefix()}menu_items WHERE item_id = '$pd[item_id]' AND site_id = '{$this->Env->site_id}' ");
		} else {
			return false;
		}
		*/
	}
	
	/**
	 * Создание пункта меню.
	 */
	protected function createItem($pd)
	{
		$is_active	 = is_numeric($pd['is_active']) ? $pd['is_active'] : 1;
		$pos		 = is_numeric($pd['pos']) ? $pd['pos'] : 0;
		$pid		 = is_numeric($pd['pid']) ? $pd['pid'] : 0;
		$folder_id	 = is_numeric($pd['folder_id']) ? $pd['folder_id'] : 0;
		$direct_link = empty($pd['direct_link']) ? 'NULL' : $this->DB->quote($pd['direct_link']);
		$title		 = strlen($pd['title']) > 0 ? $this->DB->quote($pd['title']) : 'NULL';

		$sql = "
			INSERT INTO {$this->DB->prefix()}menu_items
				(pid, site_id, pos, is_active, folder_id, group_id, direct_link, title)
			VALUES
				('$pid', '{$this->Env->site_id}', '$pos', '$is_active', $folder_id, '{$this->menu_group_id}', $direct_link, $title) ";				
		$this->DB->exec($sql);
	}

	/**
	 * Вызывается при создании ноды.
	 * 
	 * @return array $params
	 */
	public function createNode()
	{
		$this->DB->import(dirname(__FILE__) . '/sql/install', array(
			'prefix' => trim($this->DB->prefix()),
			));
		$params = parent::createNode();
		return $params;
	}
}