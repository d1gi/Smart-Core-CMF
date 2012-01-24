<?php 
/**
 * Класс с административными методами.
 * 
 * @version 2012-01-24.0
 */
class Module_Texter_Admin extends Module_Texter implements Admin_ModuleInterface
{
	/**
	 * Управление через панель управления.
	 *
	 * @param string $uri_path - часть ури
	 * @return array
	 */
	public function admin($uri_path)
	{
		// Обработчик POST данных. @todo переделать.
		if (isset($_POST['action'])) {
			switch ($_POST['action']) {
				case 'update_text_item':
					$this->updateText($_POST['item_id'], trim($_POST['pd']['text']));
					break;
				default;
			}
		}
		
		if (isset($_GET['del_item']) and is_numeric($_GET['del_item'])) {
			$this->deleteText($_GET['del_item']);
			cmf_redirect(HTTP_ROOT . ADMIN . '/module/Texter/');
		}

		$this->View->setTpl(DIR_MODULES . 'Texter/Admin.tpl');
		
		$result = $this->DB->query("SHOW TABLES LIKE '{$this->DB->prefix()}text_items' ");
		if ($result->rowCount() == 0) {
			return false;
		}

		$uri_path_parts = explode('/', $uri_path);
		
		if (isset($uri_path_parts[0]) and is_numeric($uri_path_parts[0])) {
			// Редактирование записи.
			$this->Breadcrumbs->add($uri_path_parts[0] . '/', 'Редактирование записи: ' . $uri_path_parts[0]);
			$text_item = $this->getText($uri_path_parts[0]);
			$this->View->edit_form_data = array(
				'class' => 'texter-edit',
				'action' => HTTP_ROOT . ADMIN . '/module/Texter/',
				'hiddens' => array(
					'action' => 'update_text_item',
					'item_id' => $uri_path_parts[0],
					),
				'elements' => array(
					'pd[text]' => array(
						'type' => 'textarea',
						'value' => $text_item['text'],
						),
					),
				'autofocus' => 'pd[text]',
				'buttons' => array(
					'submit[save]' => array(
						'type' => 'submit',
						'value' => 'Сохранить изменения',
						),
					'submit[cancel]' => array(
						'type' => 'submit',
						'value' => 'Отменить',
						),
					),
				);
		} else {
			// Получение списка всех текстовых записей.
			// @todo постраничность.			

			$page_num = (isset($_GET['page']) and is_numeric($_GET['page'])) ? $_GET['page'] : 1;
			$items_per_page = 20;
			
			if ($items_per_page == 0) {
				$limit = '';
			} else {
				$start_item = ($page_num - 1) * $items_per_page;
				$limit = " LIMIT $start_item, {$items_per_page} ";
			}

			$this->View->all_items = array();
			$sql = "SELECT item_id, text
				FROM {$this->DB->prefix()}text_items
				WHERE site_id = '{$this->Env->site_id}'
				ORDER BY item_id DESC
				$limit ";
			$result = $this->DB->query($sql);
			while ($row = $result->fetchObject()) {
				$this->View->all_items[$row->item_id] = array(
					//'text' => $row->text,
					'content_length' => mb_strlen($row->text),
					'nodes' => $this->getItemDataById($row->item_id),
					);
			}

			// Постраничность 
			$sql = "SELECT count(item_id) AS cnt
				FROM {$this->DB->prefix()}text_items
				WHERE site_id = '{$this->Env->site_id}' ";
			$row = $this->DB->getRow($sql);
			$this->View->pages = new Helper_Paginator(array(
					'items_count' => $row['cnt'],
					'items_per_page' => 20,
					'current_page' => $page_num,
					'link_tpl' => '?page={PAGE}',
					)
				);
		}
	}
	
	/**
	 * Удаление текста.
	 *
	 * @param
	 * @return bool
	 */
	public function deleteText($item_id)
	{
		$sql = "DELETE FROM {$this->DB->prefix()}text_items
			WHERE site_id = '{$this->Env->site_id}'
			AND item_id = {$this->DB->quote($item_id)} ";
		$this->DB->exec($sql);
		
		$sql = "DELETE FROM {$this->DB->prefix()}text_items_history
			WHERE site_id = '{$this->Env->site_id}'
			AND item_id = {$this->DB->quote($item_id)} ";
		$this->DB->exec($sql);
		
		return true;
	}
	
	/**
	 * Получить данные о записи по её id.
	 *
	 * @param int $item_id
	 * @return array
	 * 
	 * @todo оптимизировать работу, а то сейчас каждый раз ломится в бд.
	 */
	public function getItemDataById($item_id)
	{
		$data = array();
		$Node = new Node();
		
		$tmp = $Node->getListByModule('Texter');
		
		foreach ($tmp as $key => $value) {
			$params = unserialize($value['params']);
			if ($params['text_item_id'] == $item_id) {
				$data[$key] = $tmp[$key];
			}
		}
		
		return $data;
	}
	
	/**
	 * Получить элементы управления нодой.
	 * 
	 * @return array
	 */
	public function getFrontControls()
	{
		$this->default_action = 'edit';

		return array(
			'edit' => array(
				'popup_window_title' => 'Редактирование текстового блока',
				'title' => 'Редактировать',
				'link' => $this->Env->current_folder_path . ACTION . '/' . $this->Node->id . '/edit/',
				'ico' => 'edit',
				),
			/*
			'history' => array(
				'popup_window_title' => 'История изменений',
				'title' => 'История',
				'link' => $this->Env->current_folder_path . ACTION . '/' . $this->Node->id . '/history/',
				'ico' => 'edit',
				),
			*/
			);
	}
	
	/**
	 * Обработка дейсвий над нодой.
	 * 
	 * @uses Component_Editor
	 */
	public function nodeAction($params)
	{
		$uri_path_parts = explode('/', $params);
		$this->View->setTpl('Edit');
		
		$text_item = $this->getText($this->text_item_id);
		
		switch ($uri_path_parts[0]) {
			case 'edit':
				// Подключение визуального редатора.
				if ($this->editor == 1) {
					$Editor = new Component_Editor(array(
						'filemanager'	=> HTTP_ROOT . $this->Settings->getParam('module.texter.filemanager_path'),
						'editor_css'	=> $this->Settings->getParam('component.editor.editor_css'),
						));
				}
				$form_data = array(
					'class' => 'texter-edit',
					'action' => $this->Env->current_folder_path,
					'target' => '_parent',
					'hiddens' => array(
						'node_id' => $this->Node->id,
						),
					'elements' => array(
						'pd[text]' => array(
							'type' => 'textarea',
							'value' => $text_item['text'],
							'style' => 'width: 100%;height: 400px;',
							),
						),
					'autofocus' => 'pd[text]',
					'buttons' => array(
						'submit[save]' => array(
							'type' => 'submit',
							'value' => 'Сохранить изменения',
							),
						'submit[cancel]' => array(
							'type' => 'submit',
							'value' => 'Отменить',
							),
						),
					);
				$this->View->edit_form_data = $form_data;
				break;
			case 'meta':
				
				$Meta = new Component_Meta($text_item['meta']);
				$this->View->meta_controls = $Meta->getControls(array('node_id' => $this->Node->id));
				break;
			default;
		}
	
	}

	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		return array(
			'text_item_id' => array(
				'label' => 'ID текстового поля:',
				'type' => 'text',
				'value' => $this->text_item_id,
				),
			'editor' => array(
				'label' => 'Редактор:',
				'type' => 'checkbox',
				'value' => $this->editor,
				),
			);
	}

	/**
	 * Вызывается при создании ноды.
	 * 
	 * @return array params
	 * 
	 * @todo мультиязычность.
	 */
	public function createNode()
	{
		$this->DB->import(dirname(__FILE__) . '/sql/install', array(	
			'prefix' => trim($this->DB->prefix()),
			));
	
		return array(
			'text_item_id' => $this->createText(),
			'editor' => 1,
			);
	}
	
}