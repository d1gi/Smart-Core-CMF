<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Админские методы компонента: Универсальный каталог.
 * 
 * @uses Component_Editor
 * @uses Component_DatePicker
 * @uses EE
 * @uses DB
 * @uses Helper_Paginator
 * @uses spyc-0.5
 * 
 * @version 2012-01-10.0
 */
class Component_Unicat_Admin extends Component_Unicat
{
	/**
	 * Действия над каталогом. (административные)
	 *
	 * @param array $params
	 * @return array
	 */
	public function action($params)
	{
		$this->setTplPath(dirname(__FILE__) . '/');
		
		$output_data = array();
		
		// Редактирование записи.
		if (isset($_GET['edit_item']) and is_numeric($_GET['edit_item'])) {
			$this->setTpl('EditItem');
			$output_data['form_data'] = $this->getEditItemFormData($params);
		}
		// Создание новой записи.
		elseif (isset($_GET['create_item'])) {
			$this->setTpl('EditItem');
			$output_data['form_data'] = $this->getCreateItemFormData($params);
		}
		// Редактирование записей.
		elseif (isset($_GET['items'])) {
			$this->setTpl('EditItems');

			if (isset($_GET['items_per_page']) and is_numeric($_GET['items_per_page'])) {
				$this->items_per_page = $_GET['items_per_page'];
			}
			if (isset($_GET['page']) and is_numeric($_GET['page'])) {
				$this->current_page = $_GET['page'];
			}

			$options = array(
				'is_active' => 'all',
				'only_admin_properties' => true,
				//'categories' => 'all', // @todo 
				'paginator' => array(
					'items_per_page' => $this->items_per_page,
					'current_page' => $this->current_page,
					),
				);
			$output_data['items'] = $this->getItems($options);
			$output_data['pages'] = new Helper_Paginator(array(
					'items_count' => $this->getItemsCount($options),
					'items_per_page' => $this->items_per_page,
					'current_page' => $this->current_page,
					'link_tpl' => '?items&page={PAGE}',
					)
				);
		}
		// Редактирование свойств.
		elseif (isset($_GET['properties'])) {
			$this->setTpl('EditProperties');
			$output_data['new_properties_group_form_data']	= $this->getCreatePropertiesGroupFormData();
			$output_data['properties_groups_list']			= $this->getPropertiesGroupsList();
		}
		// Редактирование группы свойств.
		elseif (isset($_GET['edit_properties_group']) and is_numeric($_GET['edit_properties_group'])) {
			$this->setTpl('EditPropertiesGroup');
			$output_data['create_property_form_data']		= $this->getCreatePropertyFormData($_GET['edit_properties_group']);
			//$output_data['category_relation']				= $this->getPropertyGroupCategoryRelationList($_GET['edit_properties_group']);
			$output_data['edit_properties_group_form_data']	= $this->getEditPropertiesGroupFormData($_GET['edit_properties_group']);
			$output_data['properties_list']			   		= $this->getPropertiesList($_GET['edit_properties_group'], true, true);
		}
		// Редактирование списка структур.
		elseif (isset($_GET['structures'])) {
			$this->setTpl('EditStructures');
			$output_data['node_id'] 						= $this->Node->id;
			$output_data['structures_list'] 				= $this->getStructuresList();
			$output_data['new_structure_form_data']			= $this->getCreateStructureFormData();
		}
		// Редактирование структуры.
		elseif (isset($_GET['structure']) and is_numeric($_GET['structure'])) {
			// Редактирование категории.
			if (isset($_GET['edit_category']) and is_numeric($_GET['edit_category'])) {
				$this->setTpl('EditCategory');
				$output_data['edit_category_form_data'] 	= $this->getEditCategoryFormData($_GET['structure'], $_GET['edit_category']);
			} else {
				$this->setTpl('EditCategories');
				$output_data['categories_list'] 			= $this->getCategoriesList($_GET['structure'], 0, false, 'all');
				$output_data['new_category_form_data']		= $this->getCreateCategoryFormData($_GET['structure']);
			}
		}
		// Редактирование cвойства.
		elseif (isset($_GET['edit_property']) and is_numeric($_GET['edit_property'])) {
			$this->setTpl('EditProperty');
			$output_data['edit_property_form_data'] 		= $this->getEditPropertyFormData($_GET['edit_property']);
		}
		// Управление каталогом.
		else {
			$this->setTpl('Manage');
			$output_data['structures_list']					= $this->getStructuresList();
		}
		
		return $output_data;
	}
	
	/**
	 * Получить форму создания структуры категорий.
	 *
	 * @return array
	 */
	public function getCreateStructureFormData()
	{
		return array(
			'action' => $this->action_path,
			'hiddens' => array(
				'node_id' => $this->Node->id,
				),
			'elements' => array(
				'pd[name]' => array(
					'label' => 'Техническое имя',
					'type' => 'string',
					'value' => '',
					),
				'pd[descr]' => array(
					'label' => 'Описание',
					'type' => 'string',
					'value' => '',
					),
				'pd[table]' => array(
					'label' => 'Имя таблицы',
					'type' => 'string',
					'value' => $this->prefix . 'categories_s' . $this->Env->site_id . '_e' . $this->entity_id . '_',
					),
				),
			'buttons' => array(
				'submit[create_structure]' => array(
					'type' => 'submit',
					'value' => 'Добавить',
					),
				),
			'autofocus' => 'pd[name]',
			);
	}
	
	/**
	 * Получить ссылку на создание записи.
	 *
	 * @param int $category_id - ID категории где надо создать запись.
	 * @return string - ссылка на редактирвание.
	 */
	public function getCreateItemLink($category_id = null)
	{
		//return $this->action_path . "?create_item&category_id=$category_id";
		return $this->action_path . $category_id . "?create_item";
	}
	
	/**
	 * Получить ссылку на управление категориями.
	 *
	 * @param string - путь категорий, сгеренированный парсером.
	 * @return string - ссылка на редактирвание.
	 */
	public function getManageCategoriesLink($categories_path = '')
	{
		return $this->action_path . $categories_path;
	}
	
	/**
	 * Получить форму выбора экземпляра каталога.
	 *
	 * @param bool $get_tpl_path - получить путь к шаблону.
	 * @return array|string
	 * 
	 * @todo возможно ненужно...
	 */
	public function getChooseEntityForm($get_tpl_path = false)
	{
		if ($get_tpl_path) {
			return dirname(__FILE__) . '/ChooseEntityForm.tpl';
		}

		$list = $this->getEntitiesList();
		$list[0] = '';
		ksort($list);
		
		return array(
			'action' => $this->Env->current_folder_path,
			'target' => '_parent',
			'enctype' => 'multipart/form-data',
			'hiddens' => array(
				'node_id' => $this->Node->id,
				),
			'elements' => array(
				'pd[entity_id]' => array(
					'label' => 'Выбрать экземпляр',
					'type' => 'select',
					'value' => 0,
					'options' => $list,
					),
				),				
			'buttons' => array(
				'submit[choose_entity]' => array(
					'type' => 'submit',
					'value' => 'Выбрать экземпляр',
					),
				),
			);
	}
	
	/**
	 * Получить путь к шаблону для формы создания новых таблиц.
	 *
	 * @return string
	 */
	public function getCreateTablesFormTemplate()
	{
		return dirname(__FILE__) . '/CreateTablesForm.tpl';
	}
	
	/**
	 * Получить данные формы для создания таблиц.
	 *
	 * @return array
	 */
	public function getCreateTablesFormData()
	{
		return array(
			'action' => $this->Env->current_folder_path,
			'target' => '_parent',
			'enctype' => 'multipart/form-data',
			'hiddens' => array(
				'node_id' => $this->Node->id,
				),
			'elements' => array(
				'pd[prefix]' => array(
					'label' => 'Префикс таблиц',
					'type' => 'string',
					'value' => 'unicat_',
					),
				),				
			'buttons' => array(
				'submit[create_tables]' => array(
					'type' => 'submit',
					'value' => 'Создать таблицы',
					),
				),
			'autofocus' => 'pd[prefix]',
			);
	}
	
	/**
	 * Получить путь к шаблону для формы создания новых таблиц.
	 *
	 * @param
	 * @return string
	 */
	public function getCreateEntityFormTemplate()
	{
		return dirname(__FILE__) . '/CreateEntityForm.tpl';
	}
	
	/**
	 * Получить форму выбора экземпляра каталога.
	 *
	 * @return array
	 */
	public function getCreateEntityFormData()
	{
		return array(
			'action' => $this->Env->current_folder_path,
			'target' => '_parent',
			'enctype' => 'multipart/form-data',
			'hiddens' => array(
				'node_id' => $this->Node->id,
				),
			'elements' => array(
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'string',
					'value' => '',
					),
				'pd[name]' => array(
					'label' => 'Имя (только енг)',
					'type' => 'string',
					'value' => '',
					),
				),				
			'buttons' => array(
				'submit[create_entity]' => array(
					'type' => 'submit',
					'value' => 'Создать экземпляр',
					),
				),
			'autofocus' => 'pd[title]',
			);
	}

	/**
	 * Получить данные формы создания категории.
	 *
	 * @param int $structure_id
	 * @return array
	 */
	public function getCreateCategoryFormData($structure_id)
	{
		$categories = array(0 => '[ Корневая категория ]');
		foreach ($this->getCategoriesList($structure_id, 0) as $key => $value) {
			$pfx = ' ';
			while($value['level']--) {
				$pfx .= ' .. ';
			}
			$categories[$key] = $pfx . $value['title'];
		}
		
		return array(
			'action' => $this->action_path,
			'hiddens' => array(
				'node_id' => $this->Node->id,
				'pd[structure_id]' => $structure_id,
				),
			'elements' => array(
				'pd[is_active]' => array(
					'label' => 'Включено',
					'type' => 'checkbox',
					'value' => true,
					),
				'pd[pos]' => array(
					'label' => 'Позиция',
					'type' => 'string',
					'value' => 0,
					),
				'pd[pid]' => array(
					'label' => 'Родительская категория',
					'type' => 'select',
					'value' => 0,
					'options' => $categories,
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
				),
			'buttons' => array(
				'submit[create_category]' => array(
					'type' => 'submit',
					'value' => 'Добавить',
					),
				),
			'autofocus' => 'pd[title]',
			);
	}
	
	/**
	 * Получить данные формы создания новой записи.
	 *
	 * @param string $path - путь в формате "monitor/lcd/".
	 * @return array
	 */
	public function getCreateItemFormData($path)
	{
		if (strlen($path) == 0) {
			$structures = array();
		} else {
			$tmp = $this->parser($path);
			$structures = $tmp['data']['structures'];
			//$category_id = $tmp['data']['category_id'];
			unset($tmp);
		}

		$form_data = array(
			'action' => $this->Env->current_folder_path,
			'target' => '_parent',
			'enctype' => 'multipart/form-data',
			'hiddens' => array(
				'node_id' => $this->Node->id,
				),
			'elements' => array(
				'pd[is_active]' => array(
					'label' => 'Включено',
					'type' => 'checkbox',
					'value' => true,
					),
				'pd[uri_part]' => array(
					'label' => 'Часть адреса',
					'type' => 'string',
					),
				'pd[meta][keywords]' => array(
					'label' => 'Meta-Keywords',
					'type' => 'string',
					),
				'pd[meta][description]' => array(
					'label' => 'Meta-Description',
					'type' => 'string',
					),					
				),				
			'buttons' => array(
				'submit[create_item]' => array(
					'type' => 'submit',
					'value' => 'Добавить',
					),
				
				),
			);

		// Прототип записи.
		//$images = array();	
		$prototype	= $this->getItemPrototype($structures);
		
		// @todo Продумать подключение визивига!!!!
		$Editor		= new Component_Editor(array(
			'filemanager'	=> HTTP_ROOT . $this->Settings->getParam('module.texter.filemanager_path'),
			'editor_css'	=> $this->Settings->getParam('component.editor.editor_css'),
			));
		$DatePicker	= new Component_DatePicker();

		$autofocus = false;
		
		$form_fields_list = array();
		foreach ($prototype as $properties_group => $value1) {
			// @todo отображение групп свойств.
			foreach ($value1['properties'] as $key => $value) {
				$form_fields_list[] = "pd[content][$key]";

				// Вызов события, для обработки плагином.
				$data = $this->Node->event('adminEditProperty', array(
					'property_id' => $key, 
					'value' => null,
					'name' => 'pd[content][' . $key . ']',
					));
				if (!empty($data)) {
					$form_data['elements']['pd[content][' . $key . ']'] = array (
						'label' => $value['title'],
						'type' => 'html',
						'value' => $data,
						);
					continue;
				}
				
				// Чтение параметров.
				$params = strlen($value['params']) == 0 ? array() : unserialize($value['params']);
				
				if (!$autofocus) {
					switch ($value['type']) {
						case 'string':
						case 'text':
						case 'int':
							$autofocus = 'pd[content][' . $key . ']';
							break;
						default;
					}
				}
				
				switch ($value['type']) {
					case 'file':
					case 'img':
					case 'image':
						$form_data['elements'][$value['name']] = array (
							'label' => $value['title'],
							'type' => 'file',
							);
						if (!$this->Media->isActive()) {
							$form_data['elements'][$value['name']]['disabled']  = true;
						}
						array_pop($form_fields_list);
						$form_fields_list[] = $value['name'];
						break;
					case 'string':
						$form_data['elements']['pd[content][' . $key . ']'] = array (
							'label' => $value['title'],
							'type' => 'string',
							);
						break;
					case 'text':
						$form_data['elements']['pd[content][' . $key . ']'] = array (
							'label' => $value['title'],
							'type' => 'textarea',
							);
						break;
					case 'int':
					case 'double':
						$form_data['elements']['pd[content][' . $key . ']'] = array (
							'label' => $value['title'],
							'type' => 'string',
							);
						break;
					case 'checkbox':
						$form_data['elements']['pd[content][' . $key . ']'] = array (
							'label' => $value['title'],
							'type' => 'checkbox',
							);
						break;
					case 'datetime':
						$form_data['elements']['pd[content][' . $key . ']'] = array (
							'label' => $value['title'],
							'type' => 'string',
							'onclick' => $DatePicker->onfocus(),
							);
						break;
					case 'select':
						$options = array();
						if ($value['is_required'] == 0) {
							$options[''] = '';
						}
						if (isset($params['options']) and is_array($params['options'])) {
							foreach ($params['options'] as $option_value => $option_title) {
								$options[trim($option_value)] = trim($option_title);
							}
						}
						$form_data['elements']['pd[content][' . $key . ']'] = array (
							'label' => $value['title'],
							'type' => 'select',
							'options' => $options,
							);
						break;
					default;
				} // __end switch ($value['type'])
				
				// Установка значений по умолчанию.
				if (isset($params['default'])) {
					switch ($params['default']) {
						case 'null':
							$default = '';
							break;
						case 'datetime':
							$Date	 = new Helper_Date();
							$default = $Date->getDatetime();
							break;
						default;
							$default = $params['default'];
					}
					
					$form_data['elements']['pd[content][' . $key . ']']['value'] = $default;
				}
				
				// Установка фрага disabled.
				if (isset($params['disabled'])) {
					$form_data['elements']['pd[content][' . $key . ']']['disabled'] = $params['disabled'];
				}
			}
		}
		
		$form_data['autofocus'] = $autofocus;
			
		// Филдсеты.
		$form_data['fieldsets'] = array(
			'item_properties' => array(
				'title' => 'Свойства',
				'elements' => $form_fields_list,
				),
			'system_item_properties' => array(
				'title' => 'Системные параметры',
				'elements' => array(
					'pd[is_active]',
					'pd[uri_part]',
					'pd[meta][keywords]',
					'pd[meta][description]',
					),
				),
			'structures' => array(
				'title' => 'Структуры',
				'elements' => array(
					'pd[structures][0]',
					),
				),
			);

		// Структуры
		// @todo сделать обработку "вхождений", щас пока работает только как single
		$form_data['elements']['pd[structures][0]'] = array(
			'label' => 'Привязать к экземпляру',
			'type' => 'checkbox',
			'value' => 1,
			);
		
		$categories_list = array();
		foreach ($this->structures as $struct_key => $struct_value) {
			$categories_list[0] = '[ выбрать ]';
			
			foreach ($this->getCategoriesList($struct_value['id'], 0) as $key => $value) {
				$pfx = '';
				while($value['level']--) {
					$pfx .= ' .. ';
				}
				$categories_list[$key] = $pfx . $value['title'];
			}
			
			// Одиночные вхождения.
			if ($struct_value['entries'] === 'single' or (int) $struct_value['entries'] === 1) {
				$tmp = '';
				if (isset($structs_tmp[$struct_value['id']])) {
					foreach ($structs_tmp[$struct_value['id']] as $key => $value) {
						$tmp = $key;
						break;
					}
				}
				$form_data['elements']["pd[structures][$struct_value[id]]"] = array(
					'label' => $struct_value['descr'],
					'type' => 'select',
					'value' => $tmp,
					'options' => $categories_list,
					);
			}
			
			// Множественные вхождения.
			else if ($struct_value['entries'] === 'multi' or $struct_value['entries'] == 0) {
				$checkbox_block = '';
				foreach ($categories_list as $category_id => $category_value) {
					if ($category_id == 0) {
						continue;
					}
					$id = "id-pd-structures--$struct_value[id]-inner-$category_id";
					$pd = "pd[structures][$struct_value[id]][$category_id]";
					$checked = isset($structs_tmp[$struct_value['id']][$category_id]) ? ' checked="checked"' : '';
					$checkbox_block .= "\t<input type=\"hidden\" name=\"$pd\" value=\"0\"/><input name=\"$pd\" value=\"1\"$checked id=\"$id\" type=\"checkbox\"/><label for=\"$id\"> $category_value</label><br />\n";
				}
				
				$form_data['elements']["pd[structures][$struct_value[id]]"] = array(
					'label' => $struct_value['descr'],
					'type' => 'html',
					'value' => $checkbox_block,
					);
			}
			
			// @todo сделать обработку "ограниченных множественных вхождений".
			
			$form_data['fieldsets']['structures']['elements'][] = "pd[structures][$struct_value[id]]";
		}

		if (count($categories_list) === 0) {
			unset($form_data['elements']['pd[structures][0]']);
			unset($form_data['fieldsets']['structures']);
		}
		
		return $form_data;
	}

	/**
	 * Получить данные формы создания свойства в группе.
	 *
	 * @param int $properties_group_id
	 * @return array
	 */
	public function getCreatePropertyFormData($properties_group_id)
	{
		return array(
			'action' => $this->action_path,
			'hiddens' => array(
				'node_id' => $this->Node->id,
				'pd[properties_group_id]' => $properties_group_id,
				),
			'elements' => array(
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'string',
					'value' => '',
					),
				'pd[name]' => array(
					'label' => 'Техническое имя',
					'type' => 'string',
					'value' => '',
					),
				'pd[type]' => array(
					'label' => 'Тип',
					'type' => 'select',
					'options' => array(
						'string' => 'Строка (string)',
						'text' => 'Текст (text)',
						'int' => 'Целое число (int)',
						'double' => 'Дробное число (double)',
						'checkbox' => 'Флаг состояни (checkbox)', // @todo сделать поддержку в БД.
						'date' => 'Дата (date)',
						'datetime' => 'Дата и время (datetime)',
						'img' => 'Изображение (img)',
						'file' => 'Файл (file)',
						'select' => 'Одиночный выбор (select)',
						'multiselect' => 'Множественный выбор (multiselect)', // @todo сделать поддержку в БД.
						),
					'value' => 'string',
					),
				'pd[is_required]' => array(
					'label' => 'Обязателен для заполнения',
					'type' => 'checkbox',
					'value' => false,
					),
				'pd[show_in_admin]' => array(
					'label' => 'Отображать в списке админа',
					'type' => 'checkbox',
					'value' => false,
					),
				'pd[show_in_list]' => array(
					'label' => 'Отображать в списке записей',
					'type' => 'checkbox',
					'value' => false,
					),
				'pd[show_in_view]' => array(
					'label' => 'Отображать при просмотре записи',
					'type' => 'checkbox',
					'value' => true,
					),
				),
			'buttons' => array(
				'submit[create_property]' => array(
					'type' => 'submit',
					'value' => 'Создать свойство',
					),
				),
			'autofocus' => 'pd[title]',
			);
	}
	
	/**
	 * Получить данные формы создания новой группы свойств.
	 *
	 * @param void
	 * @return array
	 */
	public function getCreatePropertiesGroupFormData()
	{
		return array(
			'action' => $this->action_path,
			'hiddens' => array(
				'node_id' => $this->Node->id,
				),
			'elements' => array(
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'string',
					'value' => '',
					),
				'pd[name]' => array(
					'label' => 'Техническое имя',
					'type' => 'string',
					'value' => '',
					),
				),
			'buttons' => array(
				'submit[create_properties_group]' => array(
					'type' => 'submit',
					'value' => 'Добавить группу свойств', // @todo 
					),
				),
			'autofocus' => 'pd[title]',
			);
	}
	
	/**
	 * Получить данные формы редактирования категории.
	 *
	 * @param int $structure_id
	 * @param int $category_id
	 * @return array
	 */
	public function getEditCategoryFormData($structure_id, $category_id)
	{
		$data = $this->getCategoryData($structure_id, $category_id);

		$categories = array(0 => '[Корневая категория]');
		foreach ($this->getCategoriesList($structure_id, 0) as $key => $value) {
			$pfx = ' ';
			while($value['level']--) {
				$pfx .= ' .. ';
			}
			$categories[$key] = $pfx . $value['title'];
		}
		
		if (count($this->getCategoryInheritanceList($structure_id, $category_id)) == 0 and $this->getItemsCount(array('categories' => $category_id)) == 0) {
			$disabled = false;
			$delete_category_msg = '';
		} else {
			$disabled = true;
			$delete_category_msg = ' (можно только пустую категорию)';
		}
		
		return array(
			'action' => $this->action_path,
			'hiddens' => array(
				'node_id' => $this->Node->id,
				'pd[category_id]' => $data['category_id'],
				'pd[structure_id]' => $structure_id,
				),
			'elements' => array(
				'pd[is_active]' => array(
					'label' => 'Включено',
					'type' => 'checkbox',
					'value' => $data['is_active'],
					),
				'pd[is_inheritance]' => array(
					'label' => 'Наследование',
					'type' => 'checkbox',
					'value' => $data['is_inheritance'],
					),
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'string',
					'value' => $data['title'],
					),
				'pd[uri_part]' => array(
					'label' => 'Часть адреса',
					'type' => 'string',
					'value' => $data['uri_part'],
					),
				'pd[pos]' => array(
					'label' => 'Позиция',
					'type' => 'string',
					'value' => $data['pos'],
					),
				'pd[pid]' => array(
					'label' => 'Родительская категория',
					'type' => 'select',
					'value' => $data['pid'],
					'options' => $categories,
					),
				'pd[meta][keywords]' => array(
					'label' => 'Meta-Keywords',
					'type' => 'string',
					'value' => @$data['meta']['keywords'],
					),
				'pd[meta][description]' => array(
					'label' => 'Meta-Description',
					'type' => 'string',
					'value' => @$data['meta']['description'],
					),					
				),				
			'buttons' => array(
				'submit[upadate_category]' => array(
					'type' => 'submit',
					'value' => 'Сохранить изменения',
					),
				'submit[delete_category]' => array(
					'type' => 'submit',
					'value' => 'Удалить' . $delete_category_msg,
					'onclick' => "return confirm('Вы уверены, что хотите удалить категорию?')",
					'disabled' => $disabled,
					),
				'submit[cancel]' => array(
					'type' => 'submit',
					'value' => 'Отменить',
					'onclick' => 'history.back(); return false;',
					),
				),
			'autofocus' => 'pd[title]',
			);
	}

	/**
	 * Получить данные формы редактирования записи.
	 *
	 * @param string $path - путь в формате "monitor/lcd/".
	 * @return array
	 */
	public function getEditItemFormData($path)
	{
		// @todo убрать отсюда $_GET['edit_item']
		if (isset($_GET['edit_item']) and is_numeric($_GET['edit_item'])) {
			$item_id = $_GET['edit_item'];
		} else {
			return false;
		}

		if (strlen($path) == 0) {
			$structures = array();
		} else {
			$tmp = $this->parser($path);
			$structures = $tmp['data']['structures'];
			//$category_id = $tmp['data']['category_id'];
			unset($tmp);
		}
		
		// @todo ПЕРЕДЕЛАТЬ! :)
		$item = $this->getItem($item_id, array('categories' => 1));
		$current_category_id = 1;
		if (isset($item['categories'])) {
			foreach ($item['categories'] as $key => $value) {
				$current_category_id = $key;
				break;
			}
		}

		$form_data = array(
			'action' => $this->Env->current_folder_path,
			'target' => '_parent',
			'enctype' => 'multipart/form-data',
			'hiddens' => array(
				'node_id' => $this->Node->id,
				'pd[item_id]' => $item['item_id'],
				),
			'elements' => array(
				'pd[is_active]' => array(
					'label' => 'Включено',
					'type' => 'checkbox',
					'value' => $item['is_active'],
					),
				'pd[create_datetime]' => array(
					'label' => 'Запись создана:',
					'type' => 'string',
					'value' => $item['create_datetime'],
					'disabled' => true,
					),
				'pd[uri_part]' => array(
					'label' => 'Часть адреса',
					'type' => 'string',
					'value' => $item['uri_part'],
					),
				'pd[meta][keywords]' => array(
					'label' => 'Meta-Keywords',
					'type' => 'string',
					'value' => @$item['meta']['keywords'],
					),
				'pd[meta][description]' => array(
					'label' => 'Meta-Description',
					'type' => 'string',
					'value' => @$item['meta']['description'],
					),
				),				
			'buttons' => array(
				'submit[update_item]' => array(
					'type' => 'submit',
					'value' => 'Сохранить изменения',
					),
				'submit[delete_item]' => array(
					'type' => 'submit',
					'value' => 'Удалить',
					'onclick' => "return confirm('Вы уверены, что хотите удалить запись?')",
					),
				'submit[cancel]' => array(
					'type' => 'submit',
					'value' => 'Отменить',
					),
				),
			);

		//$images = array();	
		$prototype	= $this->getItemPrototype($structures);
		// @todo Продумать подключение визивига!!!!
		$Editor		= new Component_Editor(array(
			'filemanager'	=> HTTP_ROOT . $this->Settings->getParam('module.texter.filemanager_path'),
			'editor_css'	=> $this->Settings->getParam('component.editor.editor_css'),
			));
		$DatePicker	= new Component_DatePicker();
		
		// Для начала создаётся массив с полями формы, который берется из прототипа записи. 
		// Затем на него накладываются существующие свойства записи.
		$form_fields = array();
		foreach ($prototype as $properties_group => $value1) {
			foreach ($value1['properties'] as $key => $value) {
				$form_fields[$value['name']] = array(
					'property_id'	=> (string) $key,
					'type'			=> $value['type'],
					'title'			=> $value['title'],
					'original_value'=> '',
					'value'			=> '',
					'params'		=> $value['params'],
					'is_required'	=> $value['is_required'],
					'show_in_view'	=> $value['show_in_view'],
					'show_in_list'	=> $value['show_in_list'],
					);
			}
		}
		
		foreach ($item['content'] as $key => $value) {
			$form_fields[$key] = $value;
		}
		
		$autofocus = false;
		
		// Контент записи
		$form_fields_list = array();
		foreach ($form_fields as $key => $value) {
			$form_fields_list[] = "pd[content][$value[property_id]]";
			
			// Вызов события, для обработки плагином.
			$data = $this->Node->event('adminEditProperty', array(
				'property_id' => $value['property_id'], 
				'value' => $value['value'],
				'name' => 'pd[content][' . $value['property_id'] . ']',
				));
			if (!empty($data)) {
				$form_data['elements']['pd[content][' . $value['property_id'] . ']'] = array (
					'label' => $value['title'],
					'type' => 'html',
					'value' => $data,
					);
				continue;
			}
			
			// Чтение параметров.
			$params = strlen($value['params']) == 0 ? array() : unserialize($value['params']);
			
			if (!$autofocus) {
				switch ($value['type']) {
					case 'string':
					case 'text':
					case 'int':
						$autofocus = 'pd[content][' . $value['property_id'] . ']';
						break;
					default;
				}
			}
			
			switch ($value['type']) {
				case 'file':
					// Файл есть, по этому она отображается и добавляется крыжик, чтобы удалить её.
					if (strlen($value['value']) > 0) {
						$form_data['elements'][$key] = array (
							'label' => $value['title'],
							'type' => 'html',
							'value' => $value['value'] . "\n\t" .
								'<input type="hidden" name="pd[_delete_][' . $key . ']" value="0" />' . "\n\t" .
								'<div><input type="checkbox" name="pd[_delete_][' . $key . ']" id="pd-_delete_-' . $key . '" value="1" />' . "\n\t" .
								'<label for="pd-_delete_-' . $key . '">Удалить</label></div>',
							);
					}
					// Файла нет, по этому предлагается загрузить его.
					else {
						$form_data['elements'][$key] = array (
							'label' => $value['title'],
							'type' => 'file',
							);
					}
					
					if (!$this->Media->isActive()) {
						$form_data['elements'][$key]['disabled']  = true;
					}
					
					array_pop($form_fields_list);
					$form_fields_list[] = $key;
					break;
				case 'img':
					// @todo сделать через массив $images, чтобы соблюдался порядок свойств в записи.
					
					// Картинка есть, по этому она отображается и добавляется крыжик, чтобы удалить её.
					if (strlen($value['value']) > 0) {
						$form_data['elements'][$key] = array (
							'label' => $value['title'],
							'type' => 'html',
							'value' => '<img src="' . $value['value'] . '" alt="' . $value['value'] . '"/>' . "\n\t" .
								'<input type="hidden" name="pd[_delete_][' . $key . ']" value="0" />' . "\n\t" .
								'<div><input type="checkbox" name="pd[_delete_][' . $key . ']" id="pd-_delete_-' . $key . '" value="1" />' . "\n\t" .
								'<label for="pd-_delete_-' . $key . '">Удалить</label></div>',
							);
					}
					// Картинки нет, по этому предлагается загрузить её.
					else {
						$form_data['elements'][$key] = array (
							'label' => $value['title'],
							'type' => 'file',
							);
					}

					if (!$this->Media->isActive()) {
						$form_data['elements'][$key]['disabled']  = true;
					}

					array_pop($form_fields_list);
					$form_fields_list[] = $key;
					break;
				case 'select':
					$options = array();
					if ($value['is_required'] == 0) {
						$options[''] = '';
					}
					
					if (isset($params['options']) and is_array($params['options'])) {
						foreach ($params['options'] as $option_value => $option_title) {
							$options[trim($option_value)] = trim($option_title);
						}
					}
				
					$form_data['elements']['pd[content][' . $value['property_id'] . ']'] = array (
						'label' => $value['title'],
						'type' => 'select',
						'value' => $value['original_value'],
						'options' => $options,
						);
					break;
				case 'string':
					$form_data['elements']['pd[content][' . $value['property_id'] . ']'] = array (
						'label' => $value['title'],
						'type' => 'string',
						'value' => $value['value'],
						);
					break;
				case 'int':
				case 'double':
					$form_data['elements']['pd[content][' . $value['property_id'] . ']'] = array (
						'label' => $value['title'],
						'type' => 'string',
						'value' => $value['value'],
						);
					break;
				case 'text':
					$form_data['elements']['pd[content][' . $value['property_id'] . ']'] = array (
						'label' => $value['title'],
						'type' => 'textarea',
						'value' => $value['value'],
						);
					break;
				case 'checkbox':
					$form_data['elements']['pd[content][' . $value['property_id'] . ']'] = array (
						'label' => $value['title'],
						'type' => 'checkbox',
						'value' => $value['value'],
						);
					break;
				case 'datetime':
					$form_data['elements']['pd[content][' . $value['property_id'] . ']'] = array (
						'label' => $value['title'],
						'type' => 'string',
						'onclick' => $DatePicker->onfocus(),
						'value' => $value['value'],
						);
					break;
				default;
			}
			
			// Установка фрага disabled.
			if (isset($params['disabled'])) {
				$form_data['elements']['pd[content][' . $value['property_id'] . ']']['disabled'] = $params['disabled'];
			}
		}
		
		$form_data['autofocus'] = $autofocus;

		// Филдсеты.
		$form_data['fieldsets'] = array(
			'item_properties' => array(
				'title' => 'Свойства',
				'elements' => $form_fields_list,
			),
			'system_item_properties' => array(
				'title' => 'Системные параметры',
				'elements' => array(
					'pd[is_active]',
					'pd[create_datetime]',
					'pd[uri_part]',
					'pd[meta][keywords]',
					'pd[meta][description]',
				),
			),
			'structures' => array(
				'title' => 'Структуры',
				'elements' => array(
					'pd[structures][0]',
				),
			),
		);
		
		// Структуры
		$structs_tmp = array();

		$sql = "SELECT count(item_id) AS cnt
			FROM {$this->prefix}items_structures_relation
			WHERE entity_id = '{$this->entity_id}'
			AND item_id = '$item_id'
			AND structure_id = 0
			AND category_id = 0 ";
		$structs_tmp[0] = $this->DB->getRowObject($sql)->cnt == 1 ? 1 : 0;
		
		$sql = "SELECT category_id, structure_id
			FROM {$this->prefix}items_structures_relation_single
			WHERE entity_id = '{$this->entity_id}'
			AND item_id = '$item_id' ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$structs_tmp[$row->structure_id][$row->category_id] = $row->category_id;
		}
		
		$form_data['elements']['pd[structures][0]'] = array(
			'label' => 'Привязать к экземпляру',
			'type' => 'checkbox',
			'value' => $structs_tmp[0],
			);
		$categories_list = array();
		foreach ($this->structures as $struct_key => $struct_value) {
			$categories_list[0] = '[ выбрать ]';
			
			foreach ($this->getCategoriesList($struct_value['id'], 0) as $key => $value) {
				$pfx = '';
				while($value['level']--) {
					$pfx .= ' .. ';
				}
				$categories_list[$key] = $pfx . $value['title'];
			}

			// Одиночные вхождения.
			if ($struct_value['entries'] === 'single' or (int) $struct_value['entries'] === 1) {
				$tmp = '';
				if (isset($structs_tmp[$struct_value['id']])) {
					foreach ($structs_tmp[$struct_value['id']] as $key => $value) {
						$tmp = $key;
						break;
					}
				}
				$form_data['elements']["pd[structures][$struct_value[id]]"] = array(
					'label' => $struct_value['descr'],
					'type' => 'select',
					'value' => $tmp,
					'options' => $categories_list,
					);
			}
			// Множественные вхождения.
			else if ($struct_value['entries'] === 'multi' or $struct_value['entries'] == 0) {
				$checkbox_block = '';
				foreach ($categories_list as $category_id => $category_value) {
					if ($category_id == 0) {
						continue;
					}
					$id = "id-pd-structures--$struct_value[id]-inner-$category_id";
					$pd = "pd[structures][$struct_value[id]][$category_id]";
					$checked = isset($structs_tmp[$struct_value['id']][$category_id]) ? ' checked="checked"' : '';
					$checkbox_block .= "\t<input type=\"hidden\" name=\"$pd\" value=\"0\"/><input name=\"$pd\" value=\"1\"$checked id=\"$id\" type=\"checkbox\"/><label for=\"$id\"> $category_value</label><br />\n";
				}
				
				$form_data['elements']["pd[structures][$struct_value[id]]"] = array(
					'label' => $struct_value['descr'],
					'type' => 'html',
					'value' => $checkbox_block,
					);
			}
			
			// @todo сделать обработку "ограниченных множественных вхождений".
			
			$form_data['fieldsets']['structures']['elements'][] = "pd[structures][$struct_value[id]]";
		}

		if (isset($_GET['admin'])) {
			unset($form_data['target']);
			$form_data['hiddens']['return_to'] = $this->action_path . '?items';
		}
			
		if (count($categories_list) === 0) {
			unset($form_data['elements']['pd[structures][0]']);
			unset($form_data['fieldsets']['structures']);
		}
		
		return $form_data;
	}
	
	/**
	 * Получить данные формы редактирования свойства.
	 *
	 * @param int $property_id
	 * @return array
	 */
	public function getEditPropertyFormData($property_id)
	{
		$tmp = $this->getPropertiesList(false, true, true);
		$property = $tmp[$property_id];
		$delete_disabled = (isset($property['items_count']) and (int)$property['items_count'] === 0) ? false : true;
		
		// Проверка поля на необходимость, т.е. их нельзя удалить.
		if (!empty($this->requred_items_properties)) {
			foreach ($this->requred_items_properties as $prop_group => $val) {
				foreach ($val['properties'] as $key => $value) {
					if ($property['name'] == $key) {
						$delete_disabled = true;
						continue;
					}
				}
			}
		}
		
		return array(
			'action' => $this->action_path,
			'hiddens' => array(
				'node_id' => $this->Node->id,
				'pd[property_id]' => $property_id,
				),
			'elements' => array(
				'pd[is_active]' => array(
					'label' => 'Включено?',
					'type' => 'checkbox',
					'value' => $property['is_active'],
					),
				'pd[__type]' => array(
					'label' => 'Тип',
					'type' => 'html',
					'value' => $property['type'],
					),
				'pd[__name]' => array(
					'label' => 'Техническое имя',
					'type' => 'html',
					'value' => $property['name'],
					),
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'string',
					'value' => $property['title'],
					),
				'pd[pos]' => array(
					'label' => 'Позиция',
					'type' => 'string',
					'value' => $property['pos'],
					),
				'pd[properties_group_id]' => array(
					'label' => 'Группа свойств',
					'type' => 'select',
					'options' => $this->getPropertiesGroupsList(),
					'value' => $property['properties_group_id'],
					),
				'pd[params_yaml]' => array(
					'label' => 'Параметры (YML)',
					'type' => 'textarea',
					'value' => $property['params_yaml'],
					),
				'pd[is_required]' => array(
					'label' => 'Обязателен для заполнения',
					'type' => 'checkbox',
					'value' => $property['is_required'],
					),
				'pd[show_in_admin]' => array(
					'label' => 'Отображать в списке админа',
					'type' => 'checkbox',
					'value' => $property['show_in_admin'],
					),
				'pd[show_in_list]' => array(
					'label' => 'Отображать в списке записей',
					'type' => 'checkbox',
					'value' => $property['show_in_list'],
					),
				'pd[show_in_view]' => array(
					'label' => 'Отображать при просмотре записи',
					'type' => 'checkbox',
					'value' => $property['show_in_view'],
					),
				'pd[empty_as_null]' => array(
					'label' => 'Использовать NULL вместо пустого значения',
					'type' => 'checkbox',
					'value' => $property['empty_as_null'],
					),
				),
			'buttons' => array(
				'submit[update_property]' => array(
					'type' => 'submit',
					'value' => 'Сохранить свойство',
					),
				'submit[delete_property]' => array(
					'type' => 'submit',
					'value' => 'Удалить',
					'onclick' => "return confirm('Вы уверены, что хотите удалить запись?')",
					'disabled' => $delete_disabled,
					),
				'submit[cancel]' => array(
					'type' => 'submit',
					'value' => 'Отменить',
					'onclick' => 'history.back(); return false;',
					),
				),
			'autofocus' => 'pd[title]',
			);
	}
	
	/**
	 * Получить данные формы редактирования группы свойств.
	 *
	 * @param int $properties_group_id
	 * @return array
	 */
	public function getEditPropertiesGroupFormData($properties_group_id)
	{
		$relations = array(0 => false);
		foreach ($this->structures as $value) {
			$relations[$value['id']] = -1;
		}

		$sql = "SELECT category_id, structure_id 
			FROM {$this->prefix}properties_groups_structures_relation
			WHERE site_id = '{$this->Env->site_id}'
			AND entity_id = '{$this->entity_id}'
			AND properties_group_id = '$properties_group_id' ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			if ($row->structure_id == 0 and $row->category_id == 0) {
				$relations[0] = true;
				continue;
			}
			$relations[$row->structure_id] = $row->category_id;
		}

		$data = $this->getPropertiesGroupsList($properties_group_id);
		$form_data = array(
			'action' => $this->action_path,
			'hiddens' => array(
				'node_id' => $this->Node->id,
				'pd[properties_group_id]' => $properties_group_id,
				),
			'elements' => array(
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'string',
					'value' => $data[$properties_group_id]['title'],
					),
				'pd[name]' => array(
					'label' => 'Техническое имя',
					'type' => 'string',
					'value' => $data[$properties_group_id]['name'],
					),
				'pd[pos]' => array(
					'label' => 'Позиция',
					'type' => 'string',
					'value' => $data[$properties_group_id]['pos'],
					),
				/*
				'pd[categories_relation]' => array(
					'label' => 'Привязка к категориям (@todo)',
					'type' => 'string',
					'value' => 0,
					),
				*/
				),
			'buttons' => array(
				'submit[update_properties_group]' => array(
					'type' => 'submit',
					'value' => 'Сохранить изменения',
					),
				),
			'autofocus' => 'pd[title]',
			);

		// Структуры
		// @todo сделать обработку "вхождений", щас пока работает только как single
		
		$form_data['elements']['pd[structures][0]'] = array(
			'label' => 'Привязать к экземпляру',
			'type' => 'checkbox',
			'value' => $relations[0],
			);
		
		foreach ($this->structures as $struct_key => $struct_value) {
			$categories_list = array();
			$categories_list[-1] = '[ Не выбрана ]';
			$categories_list[0] = '[ Все категории ]';
			
			foreach ($this->getCategoriesList($struct_value['id'], 0) as $key => $value) {
				$pfx = '';
				while($value['level']--) {
					$pfx .= ' .. ';
				}
				$categories_list[$key] = $pfx . $value['title'];
			}
			
			$form_data['elements']["pd[structures][$struct_value[id]]"] = array(
				'label' => $struct_value['descr'],
				'type' => 'select',
				'value' => $relations[$struct_value['id']],
				'options' => $categories_list,
				);
			//$form_data['fieldsets']['structures']['elements'][] = "pd[structures][$struct_value[id]]";
		}

		return $form_data;
	}
	
	/**
	 * Обработчик события создания ноды.
	 *
	 * @access public
	 * @return bool
	 */
	public function _systemCreateNode()
	{
		// Проверка на наличие таблиц и их создание в случае отсуствия.
		return true;
	}
	
	// ====================================================================================================================================
	// Ниже описаны базовые методы юниката, которые являются только админскими.
	// ====================================================================================================================================

	/**
	 * Создать набор базовый набор таблиц.
	 *
	 * @return bool
	 */
	public function createTables($prefix)
	{
		$this->DB->import(dirname(__FILE__) . '/sql/install', array('prefix' => trim($prefix)));
	}
	
	/**
	 * Создать новый экземляр каталога.
	 * 
	 * @param string $name
	 * @param string $title
	 * @return $entity_id
	 */
	public function createEntity($name, $title)
	{
		$name  = $this->DB->quote($name);
		$title = $this->DB->quote($title);
		
		$empty_structures = $this->DB->quote(serialize(array()));
		
		$sql = "
			INSERT INTO {$this->prefix}entities
				(name, title, language_id, site_id, create_datetime, owner_id, structures)
			VALUES
				($name, $title, '{$this->Env->language_id}', '{$this->Env->site_id}', NOW(), '{$this->Env->user_id}', $empty_structures ) ";
		$this->DB->query($sql);
		$entity_id = $this->DB->lastInsertId();
		$this->entity_id = $entity_id;

		if (!empty($this->requred_structures) and is_array($this->requred_structures)) {
			foreach ($this->requred_structures as $structure_name => $structure_value) { // @todo 
			
			}
		}
		
		if (!empty($this->requred_items_properties)) {
			
			include_once DIR_LIB . 'spyc-0.5/spyc.php';
			
			foreach ($this->requred_items_properties as $prop_group => $val) {
				$properties_group_id = $this->createPropertiesGroup(array('title' => $val['title'], 'name' => $prop_group));
				foreach ($val['properties'] as $key => $value) {
					// Проверка на наличие таблицы. Если нету, то свойство создаётся.
					$sql = "SHOW TABLES LIKE '{$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$key}' ";
					$result = $this->DB->query($sql);
					if ($result->rowCount() == 0) {
						$this->createProperty(array(
							'name'			=> $key,
							'title'			=> $value['title'],
							'pos'			=> $value['pos'],
							'type'			=> $value['type'],
							'is_required'	=> $value['is_required'],
							'show_in_admin'	=> $value['show_in_admin'],
							'show_in_list'	=> $value['show_in_list'],
							'show_in_view'	=> $value['show_in_view'],
							'empty_as_null'	=> $value['empty_as_null'],
							'properties_group_id' => $properties_group_id,
							'params_yaml'	=> isset($value['params']) ? str_replace("---\n", '', Spyc::YAMLDump($value['params'])) : '', //  @todo str_replace("---\n" - это хак для спайка... надо переделать на свою обёртку Yaml.
							));
					}
				}
			}
		}

		return $entity_id;
	}
	
	/**
	 * Создание новой структуры категорий.
	 *
	 * Формат массива $data:
	 *  - name
	 *  - table
	 *  - descr
	 * 
	 * @param array $data
	 * @return bool
	 */
	public function createStructure($data)
	{
		$structures_data = $this->getStructuresList();
		
		$id = 1;
		$pos = 1;

		$structures = array(); 
		
		if (is_array($structures_data)) {
			foreach ($structures_data as $key => $value) {
				if ($value['pos'] >= $pos) {
					$pos = $value['pos'] + 1;
				}
				if ($value['id'] >= $id) {
					$id = $value['id'] + 1;
				}
				$structures[] = $value;
			}
		}

		$this->DB->import(dirname(__FILE__) . '/sql/structure', array('table' => $data['table']));
		
		$Date = new Helper_Date();
		$structures[] = array(
			'id' => $id,
			'name' => $data['name'],
			'table' => $data['table'],
			'reqired' => isset($data['reqired']) ? $data['reqired'] : false,
			'entries' => isset($data['entries']) ? $data['entries'] : 'single',
			'descr' => $data['descr'],
			'pos' => $pos,
			'create_datetime' => $Date->getDatetime(),
			);
		$sql = "
			UPDATE {$this->prefix}entities SET
				structures = {$this->DB->quote(serialize($structures))}
			WHERE entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}' ";
		$this->DB->query($sql);
		return true;
	}
	
	/**
	 * Обновление структур
	 * 
	 * ! Обновляются все сразу.
	 *
	 * @param array data
	 * @return bool
	 */
	public function updateStructure($data)
	{
		// Сортировка, а за одно учет на удаление.
		$sort = array();
		foreach ($data as $key => $value) {
			if ($value['delete'] == 0) {
				$sort[$value['pos']] = $key;
			}
		}
		ksort($sort);
		
		$structures = array();
		foreach ($sort as $key => $value) {
			$tmp			= $this->structures[$value];
			$tmp['pos']		= $data[$value]['pos'];
			$tmp['name']	= $data[$value]['name'];
			$tmp['descr']	= $data[$value]['descr'];
			$tmp['table']	= $data[$value]['table'];
			$tmp['reqired'] = $data[$value]['reqired'];
			$tmp['entries'] = $data[$value]['entries'];
			$structures[]	= $tmp;
		}
		
		$sql = "
			UPDATE {$this->prefix}entities SET
				structures = {$this->DB->quote(serialize($structures))}
			WHERE entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}' ";
		$this->DB->query($sql);
		return true;
	}
	
	/**
	 * Обновление группы свойств.
	 *
	 * @param array $data
	 * @return bool - успешность выполнения операции.
	 */
	public function updatePropertiesGroup($data)
	{
		$title = $this->DB->quote($data['title']);
		$name  = $this->DB->quote($data['name']);

		$sql = "
			UPDATE {$this->prefix}properties_groups SET
				name = $name,
				title = $title,
				pos = '$data[pos]'
			WHERE properties_group_id = '$data[properties_group_id]'
			AND entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}' ";
		$this->DB->exec($sql);
		/*
		$sql = "
			UPDATE {$this->prefix}properties_groups_translation SET
				title = $title
			WHERE properties_group_id = '$data[properties_group_id]'
			AND entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			AND language_id = '{$this->Env->language_id}' ";
		$this->DB->exec($sql);
		*/
		// Привязка групп свойств к структурам.
		$sql = "DELETE FROM {$this->prefix}properties_groups_structures_relation
			WHERE site_id = '{$this->Env->site_id}'
			AND entity_id = '{$this->entity_id}'
			AND properties_group_id = '$data[properties_group_id]' ";
		$this->DB->exec($sql);
		
		foreach ($data['structures'] as $structure_id => $value) {
			
			if ($structure_id == 0 and $value == 0) {
				continue;
			} elseif ($structure_id == 0 and $value == 1) {
				$value = 0;
			} elseif ($value == -1) {
				continue;
			}
			
			$sql = "
				INSERT INTO {$this->prefix}properties_groups_structures_relation
					(site_id, entity_id, properties_group_id, category_id, structure_id)
				VALUES
					('{$this->Env->site_id}', '{$this->entity_id}', '$data[properties_group_id]', '$value', '$structure_id') ";
			$this->DB->query($sql);
		}
		
		return true;
	}
	
	/**
	 * Обновление свойства.
	 *
	 * @param array $data
	 * @return bool - успешность выполнения операции.
	 */
	public function updateProperty($data)
	{
		$property_id = $data['property_id'];
		$title = $this->DB->quote($data['title']);
		
		$sql_params = '';
		$sql_params_yaml = '';
		
		if (isset($data['params_yaml'])) {
			try {
				$sql_params		 = 'params = ' . $this->DB->quote(serialize(Zend_Config_Yaml::decode($data['params_yaml']))) . ',';
				$sql_params_yaml = 'params_yaml = ' . $this->DB->quote($data['params_yaml']) . ',';
			} catch (Exception $e) {
				$sql_params = '';
				$sql_params_yaml = '';
			}
		}
				
		$sql = "
			UPDATE {$this->prefix}properties SET
				$sql_params
				$sql_params_yaml
				title = $title,
				pos = '$data[pos]',
				properties_group_id = '$data[properties_group_id]',
				is_active = '$data[is_active]',
				is_required = '$data[is_required]',
				show_in_admin = '$data[show_in_admin]',
				show_in_list = '$data[show_in_list]',
				show_in_view = '$data[show_in_view]',
				empty_as_null = '$data[empty_as_null]'
			WHERE property_id = '$property_id'
			AND entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}' ";
		$this->DB->exec($sql);
		/*
		$sql = "
			UPDATE {$this->prefix}properties_translation SET
				title = $title
			WHERE property_id = '$property_id'
			AND entity_id = '{$this->entity_id}'
			AND language_id = '{$this->Env->language_id}'
			AND site_id = '{$this->Env->site_id}'
			";
		*/
		$this->DB->exec($sql);
		
		// Если установлено значение empty_as_null = 1, то надо пройтись по БД и создать для всех записей, которые содержат свойства заданной группы.
		// @todo пока будет тупо создаваться NULL для всех записей в эекземпляре. надо сделать интеллектуально, 
		// притом очень желательно в виде задания, которое будет выполняться фоном по крону.
		// Также предварительно надо считывать прежнее значение empty_as_null и производить модификацию только в случае изменения.
		
		// 1. Сначала достаётся имя свойства.
		$sql = "SELECT name
			FROM {$this->prefix}properties
			WHERE site_id = '{$this->Env->site_id}'
			AND entity_id = '{$this->entity_id}'
			AND property_id = '$property_id' ";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		$property_name = $row->name;
		
		// 2. Затем запускается цикл по всему списку записей.
		$items = array();
		$sql = "SELECT item_id
			FROM {$this->prefix}items
			WHERE site_id = '{$this->Env->site_id}'
			AND entity_id = '{$this->entity_id}' ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			// Для каждой записи достаётся его свойство.
			$sql2 = "SELECT value FROM {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property_name} WHERE item_id = '{$row->item_id}'";
			$result2 = $this->DB->query($sql2);
			// Данных свойства нету - вставляется запись в БД со значением NULL.
			if ($result2->rowCount() == 0 and $data['empty_as_null'] == 1) {
				$sql3 = "INSERT INTO {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property_name} (item_id, value) VALUES ('{$row->item_id}', NULL)";
				$this->DB->query($sql3);
			} elseif ($result2->rowCount() == 1 and $data['empty_as_null'] == 0) {
				// Запись есть, если значение равно NULL, то удаляется.
				$row2 = $result2->fetchObject();
				if (strlen($row2->value) == 0) {
					$sql3 = "DELETE FROM {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property_name} WHERE item_id = '{$row->item_id}'";
					$this->DB->exec($sql3);
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Создание группы свойств.
	 *
	 * Формат $data:
	 *  - title - заголовок.
	 *  - name - техническое имя.
	 * 
	 * @param array $data
	 * @return $properties_group_id|false - ID созданной группы или false.
	 */
	public function createPropertiesGroup($data)
	{
		$title = $this->DB->quote($data['title']);
		$name  = $this->DB->quote($data['name']);
		
		// Вычисляется максимальная позиция, чтобы новое свойство было помещено последним.
		$sql = "SELECT max(pos) AS max_pos 
			FROM {$this->prefix}properties_groups
			WHERE entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}' ";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		$pos = $row->max_pos + 1;

		$sql = "
			INSERT INTO {$this->prefix}properties_groups
				(entity_id, name, title, pos, site_id)
			VALUES
				('{$this->entity_id}', $name, $title, '$pos', '{$this->Env->site_id}' ) ";
		$this->DB->query($sql);
		$properties_group_id = $this->DB->lastInsertId();
		
		// По умолчанию группа привязывается к экземпляру. (structure_id = 1 и category_id = 1)
		$sql = "
			INSERT INTO {$this->prefix}properties_groups_structures_relation
				(site_id, entity_id, properties_group_id, category_id, structure_id)
			VALUES
				('{$this->Env->site_id}', '{$this->entity_id}', '$properties_group_id', 0, 0) ";
		$this->DB->query($sql);
		
		return $properties_group_id;
	}
	
	/**
	 * Создание свойства.
	 *
	 * Формат $data:
	 *  - title - заголовок.
	 *  - name - техническое имя.
	 *  - type
	 *  - properties_group_id
	 *  - is_required
	 *  - show_in_admin
	 *  - show_in_list
	 *  - show_in_view
	 *  - empty_as_null
	 *  - pos
	 * 
	 * @param array $data
	 * @return $property_id|false - ID созданного свойства.
	 */
	public function createProperty($data)
	{
		$title = $this->DB->quote($data['title']);
		$name  = $this->DB->quote($data['name']);
		$type  = $this->DB->quote($data['type']);

		if (isset($data['params_yaml'])) {
			try {
				$params = $this->DB->quote(serialize(Zend_Config_Yaml::decode($data['params_yaml'])));
				$params_yaml = $this->DB->quote($data['params_yaml']);
			} catch (Exception $e) {
				$params = $this->DB->quote(serialize(array()));
				$params_yaml = "''";
			}
		} else {
			$params = $this->DB->quote(serialize(array()));
			$params_yaml = "''";
		}
		
		$empty_as_null = (isset($data['empty_as_null']) and $data['empty_as_null'] == 1) ? 1 : 0;
		
		if (isset($data['pos']) and is_numeric($data['pos'])) {
			$pos = $data['pos'];
		} else {
			// Вычисляется максимальная позиция, чтобы новое свойство было помещено последним.
			$sql = "SELECT max(pos) AS max_pos 
				FROM {$this->prefix}properties
				WHERE entity_id = '{$this->entity_id}'
				AND site_id = '{$this->Env->site_id}' ";
			$pos = $this->DB->getRowObject($sql)->max_pos + 1;
		}
		
		$sql = "
			INSERT INTO {$this->prefix}properties
				(entity_id, name, title, type, pos, properties_group_id, is_required, show_in_admin, show_in_list, show_in_view,
				 empty_as_null, site_id, create_datetime, owner_id, params, params_yaml )
			VALUES
				('{$this->entity_id}', $name, $title, $type, '$pos', '$data[properties_group_id]', '$data[is_required]', '$data[show_in_admin]', '$data[show_in_list]', '$data[show_in_view]',
				 $empty_as_null, '{$this->Env->site_id}', NOW(), '{$this->Env->user_id}', $params, $params_yaml ) ";
		$this->DB->query($sql);
		$property_id = $this->DB->lastInsertId();
		
		if (strlen(trim($data['name'])) == 0) {
			$data['name'] = $property_id;
			$sql = "
				UPDATE {$this->prefix}properties SET
					name = $property_id
				WHERE site_id = '{$this->Env->site_id}'
				AND entity_id = '{$this->entity_id}'
				AND property_id = $property_id ";
			$this->DB->query($sql);
		}
		
		/*
		$sql = "
			INSERT INTO {$this->prefix}properties_translation
				(property_id, entity_id, language_id, title, site_id )
			VALUES
				('$property_id', '{$this->entity_id}', '{$this->Env->language_id}', $title, '{$this->Env->site_id}' )
			";
		$this->DB->exec($sql);
		*/
		// Создание таблицы.
		$value_type = '';
		$key = '';
		switch ($data['type']) {
			case 'int':
				$value_type = 'int(10)';
				break;
			case 'string':
			case 'text':
				$value_type = 'text';
				$key = '(30)'; // используется только первые 30 символов для индексирования.
				break;
			case 'select':
			case 'multiselect':
				$value_type = 'varchar(255)';
				break;
			case 'double':
				$value_type = 'double';
				break;
			case 'checkbox':
				$value_type = 'tinyint(1)';
				break;
			case 'date':
				// $value_type = "date NOT NULL DEFAULT '0000-00-00'";
				$value_type = "date DEFAULT '0000-00-00'";
				break;
			case 'datetime':
				// $value_type = "datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
				$value_type = "datetime DEFAULT '0000-00-00 00:00:00'";
				break;
			case 'img':
			case 'image':
			case 'file':
				$value_type = 'bigint(20) unsigned';
				break;
			default;
		}
		
		$this->DB->import(dirname(__FILE__) . '/sql/property', array(
			'table' => "{$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$data['name']}",
			'key' => $key,
			'value_type' => $value_type,
			'type' => $data['type'],
			));
		return $property_id;
	}
	
	/**
	 * Удаление свойства.
	 * Возможно только в случае, если данные свойство не используется на в одной записи.
	 *
	 * @param int property_id
	 * @return bool
	 */
	public function deleteProperty($property_id)
	{
		$tmp = $this->getPropertiesList(false, true, true);

		if (isset($tmp[$property_id]) and isset($tmp[$property_id]['items_count']) and (int)$tmp[$property_id]['items_count'] === 0) {
			$property = $tmp[$property_id];
		} else {
			return false;
		}
		
		$sql = "DELETE FROM {$this->prefix}properties
			WHERE site_id = '{$this->Env->site_id}'
			AND entity_id = '{$this->entity_id}'
			AND property_id = '$property_id' ";
		$this->DB->exec($sql);
		
		$sql = "DELETE FROM {$this->prefix}properties_translation
			WHERE site_id = '{$this->Env->site_id}'
			AND entity_id = '{$this->entity_id}'
			AND property_id = '$property_id' ";
		$this->DB->exec($sql);
		
		$sql = "DROP TABLE {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property['name']}";
		$this->DB->exec($sql);
		
		return true;
	}
	 
	/**
	 * Получить список категорий в которые подключена группа свойств.
	 *
	 * @param int $properties_group_id
	 * @return array
	 * 
	 * @todo неактуальная!!! переделать!
	 */
	public function getPropertyGroupCategoryRelationList($properties_group_id)
	{
		$categories = array();
		$sql = "SELECT category_id 
			FROM {$this->prefix}properties_groups_structures_relation
			WHERE site_id = '{$this->Env->site_id}'
			AND entity_id = '{$this->entity_id}'
			AND properties_group_id = '$properties_group_id' ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$categories[$row->category_id] = $this->getCategoryData($row->category_id);
		}
		return $categories;
	}
	
	/**
	 * Получить список экземпляров.
	 *
	 * @return array
	 */
	public function getEntitiesList()
	{
		$list = array();
		$result = $this->DB->query("SHOW TABLES LIKE '{$this->prefix}entities'");
		if ($result->rowCount() == 0) {
			$list[0] = 'WARNING! Entities table does not exist.';
		} else {
			$result = $this->DB->query("SELECT * FROM {$this->prefix}entities WHERE site_id = '{$this->Env->site_id}'");
			if (!empty($result) and $result->rowCount() > 0) {
				while ($row = $result->fetchObject()) {
					$list[$row->entity_id] = $row->name;
				}
			}
		}
		return $list;
	}
}