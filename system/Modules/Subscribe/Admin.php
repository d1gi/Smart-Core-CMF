<?php 
/**
 * Класс с административными методами.
 * 
 * @version 2012-01-25.0
 */
class Module_Subscribe_Admin extends Module_Subscribe
{
	/**
	 * Конструктор.
	 */
	protected function init()
	{
		/* таблицу с рубриками думаю ненадо создавать автоматически, пускай админ сайта сам настраивает.
		$this->unicat_requred_structures = array(
			'rubrics' => array(
				'descr' => 'Рубрики рассылок',
				'table'	=> $this->Node->getParam('unicat_db_prefix') . "s{$this->Env->site_id}_e{$this->Node->getParam('entity_id')}subscribe_rubrics",
				'entries' => 'multi',
				'required' => false,
				),
			);
		*/
		$this->unicat_requred_items_properties = array(
			'releases' => array(
				'title' => 'Выпуски рассылок',
				'properties' => array(
					'subject' => array(
						'title'		=> 'Тема выпуска',
						'pos'			=> 0,
						'type'			=> 'string',
						'is_required'	=> 1,
						'show_in_admin'	=> 1,
						'show_in_list'	=> 1,
						'show_in_view'	=> 1,
						'empty_as_null'	=> 0,
						),
					'status' => array(
						'title'		=> 'Статус',
						'pos'			=> 1,
						'type'			=> 'select',
						'params'		=> array(
							'options' => array(
								'draft'		 => 'Черновик',
								'in_process' => 'В процессе рассылки',
								'stopped' 	 => 'Остановлен',
								'finished'	 => 'Завершен',
								),
							'default'	=> 'draft',
							'disabled'	=> false,
							'readonly'	=> true,
							),
						'is_required'	=> 1,
						'show_in_admin'	=> 1,
						'show_in_list'	=> 1,
						'show_in_view'	=> 1,
						'empty_as_null'	=> 0,
						),
					'auto_start_datetime' => array(
						'title'			=> 'Дата начала рассылки',
						'pos'			=> 3,
						'type'			=> 'datetime',
						'is_required'	=> 1,
						'show_in_admin'	=> 1,
						'show_in_list'	=> 1,
						'show_in_view'	=> 1,
						'empty_as_null'	=> 0,
						),
					'text' => array(
						'title'			=> 'Полный текст',
						'pos'			=> 4,
						'type'			=> 'text',
						'is_required'	=> 0,
						'show_in_admin'	=> 0,
						'show_in_list'	=> 0,
						'show_in_view'	=> 1,
						'empty_as_null'	=> 0,
						),
					),
				),
			);
		parent::init();
		$this->Unicat = new Component_Unicat_Admin($this->unicat_params);
	}
	
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		$entities_list = $this->Unicat->getEntitiesList();
		if (!isset($entities_list[0])) {
			$entities_list[0] = '[ Не выбран ]';
		}
		ksort($entities_list);
		
		$Media = new Component_Media();
		
		$node_params = array(
			'items_per_page' => array(
				'label' => 'Записей на страницу:',
				'type' => 'text',
				'value' => $this->Node->getParam('items_per_page'),
				),
			'css_prefix' => array(
				'label' => 'CSS префикс:',
				'type' => 'text',
				'value' => $this->Node->getParam('css_prefix'),
				),
			'unicat_database_id' => array(
				'label' => 'unicat_database_id:',
				'type' => 'select',
				'value' => $this->Node->getParam('unicat_database_id'),
				'options' => $this->DB_Resources->getListOptions(),
				),
			'entity_id' => array(
				'label' => 'Экземпляр каталога:',
				'type' => 'select',
				'value' => $this->Node->getParam('entity_id'),
				'options' => $entities_list,
				),
			'media_collection_id' => array(
				'label' => 'Медиа коллекция:',
				'type' => 'select',
				'options' => array_merge(array('0' => '[ Не выбрана ]'), $Media->getCollectionsList()),
				'value' => $this->Node->getParam('media_collection_id'),
				),
			'unicat_db_prefix' => array(
				'label' => 'Префикс таблиц юниката:',
				'type' => 'text',
				'value' => $this->Node->getParam('unicat_db_prefix'),
				),
			'activate_from_email' => array(
				'label' => 'Email с которого будут рассылаться письма активации',
				'type' => 'string',
				'value' => $this->activate_from_email,
				),
			'activate_from_email_subject' => array(
				'label' => 'Тема письма активации',
				'type' => 'string',
				'value' => $this->activate_from_email_subject,
				),
			);

		/*		
		$structures = array(0 => '[ Не выбрана ]');
		$tmp = $this->Unicat->getStructuresList();
		if (is_array($tmp)) {
			foreach ($tmp as $key => $value) {
				$structures[$value['id']] = $value['descr'];
			}
		}
		/*
		$node_params['rubrics_structure_id'] = array(
			'label' => 'Таблица с рубриками',
			'type' => 'select',
			'options' => $structures,
			'value' => $this->rubrics_structure_id,
			);
		*/
		return $node_params;
	}

	/**
	 * Получить элементы управления нодой.
	 * 
	 * @return array
	 */
	public function getFrontControls()
	{
		$front_controls = array();
		
		// Действие по умолчанию для ноды является "Редактировать запись"
		if ($this->View->item) {
			$this->default_action = 'edit';
			$front_controls['edit'] = array(
				'popup_window_title' => 'Редактирование записи',
				'title' => 'Редактировать',
				'link' => $this->Unicat->getEditItemLink($this->View->item['item_id']),
				'ico' => 'edit',
				);
		}
		// Действием по умолчанию для ноды является "Добавить запись"
		else if ($this->View->items) {
			
		}

		$this->default_action = 'create_item';
		$front_controls['add'] = array(
			'popup_window_title' => 'Добавить выпуск',
			'title' => 'Добавить выпуск',
			'link' => $this->Unicat->getCreateItemLink(),
			'ico' => 'edit',
			);
		$front_controls['manage_category'] = array(
			'popup_window_title' => 'Управление юникатом',
			'title' => 'Управление юникатом',
			'link' => $this->Unicat->getManageCategoriesLink(),
			'ico' => 'add',
			);
		
		return $front_controls;
	}
	
	/**
	 * Внутренние элменты управления ноды.
	 * 
	 * @access public
	 * @returns array|false
	 */
	public function getFrontControlsInner()
	{
		// @todo проверки на права юзера.
		$frontend_inner_controls = array();
		if ($this->View->items) {
			foreach ($this->View->items as $key => $value) {
				$frontend_inner_controls[$this->css_prefix . 'item_id_' . $key]['edit'] = array(
					'popup_window_title' => 'Редактировать запись',
					'title' => 'Редактировать',
					'link' => $this->Unicat->getEditItemLink($key),
					'ico' => 'edit',
					);
			}
		}
		return $frontend_inner_controls;
	}	

	/**
	 * Функция обработки действий над нодой.
	 * 
	 * @param string $params - часть адреса идущая после ключевого слова "action" в строке запроса.
	 * @return void
	 */
	public function nodeAction($params)
	{
		$this->Unicat->action($params);
		$this->View = $this->Unicat->View;
		return true;
	}
}