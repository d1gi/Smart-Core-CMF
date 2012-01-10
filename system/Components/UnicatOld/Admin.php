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
 * 
 * @version 2011-09-20.0
 */
//class Component_Unicat_Admin extends Base
class Component_UnicatOld_Admin extends Component_UnicatOld
{
	
	/**
	 * Действия над каталогом. (административные)
	 *
	 * @param $params
	 * @return
	 */
	public function action($params)
	{
		$this->setTplPath(dirname(__FILE__) . '/');
		$output_data = array();
		
		// Редактирование записи.
		if (isset($_GET['edit_item']) and is_numeric($_GET['edit_item'])) {
			$this->setTpl('EditItem');
			$output_data['form_data'] 						= $this->getEditItemFormData($params);
		}
		// Создание новой записи.
		elseif (isset($_GET['create_item'])) {
			$this->setTpl('EditItem');
			$output_data['form_data'] 						= $this->getCreateItemFormData($params);
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
				'categories' => 'all',
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
		// Редактирование группы Тэгов.
		elseif (isset($_GET['edit_tags_group']) and is_numeric($_GET['edit_tags_group'])) {
			$this->setTpl('EditTagsGroup');
			$output_data['tags_list']			   			= $this->getTagsList($_GET['edit_tags_group']);
			$output_data['create_tag_form_data']			= $this->getCreateTagFormData($_GET['edit_tags_group']);
			$output_data['edit_tags_group_form_data']		= $this->getEditTagsGroupFormData($_GET['edit_tags_group']);
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
			$output_data['category_relation']				= $this->getPropertyGroupCategoryRelationList($_GET['edit_properties_group']);
			$output_data['edit_properties_group_form_data']	= $this->getEditPropertiesGroupFormData($_GET['edit_properties_group']);
			$output_data['properties_list']			   		= $this->getPropertiesList($_GET['edit_properties_group'], true, true);
		}
		// Редактирование категории.
		elseif (isset($_GET['edit_category']) and is_numeric($_GET['edit_category'])) {
			$this->setTpl('EditCategory');
			$output_data['edit_category_form_data'] 		= $this->getEditCategoryFormData($_GET['edit_category']);
		}
		// Редактирование категорий.
		elseif (isset($_GET['categories'])) {
			$this->setTpl('EditCategories');
			$output_data['categories_list'] 				= $this->getCategoriesList(1, false, 'all');
			$output_data['new_category_form_data']			= $this->getCreateCategoryFormData();
		}
		// Редактирование cвойства.
		elseif (isset($_GET['edit_property']) and is_numeric($_GET['edit_property'])) {
			$this->setTpl('EditProperty');
			$output_data['edit_property_form_data'] 		= $this->getEditPropertyFormData($_GET['edit_property']);
		}
		// Редактирование тэга.
		elseif (isset($_GET['edit_tag']) and is_numeric($_GET['edit_tag'])) {
			$this->setTpl('EditTag');
			$output_data['edit_tag_form_data'] 				= $this->getEditTagFormData($_GET['edit_tag']);
		}
		// Редактирование тэгов.
		elseif (isset($_GET['tags'])) {
			$this->setTpl('EditTags');
			$output_data['tags_groups_list']				= $this->getTagsGroupsList();
			$output_data['new_tags_group_form_data']		= $this->getCreateTagsGroupFormData();
		}
		// Управление каталогом.
		else {
			$this->setTpl('Manage');
			$output_data['dummy']		= true;
		}
		
		return $output_data;
	}
	
	/**
	 * Получить ссылку на создание записи.
	 *
	 * @param int $category_id - ID категории где надо создать запись.
	 * @return string - ссылка на редактирвание.
	 */
	public function getCreateItemLink($category_id = null)
	{
//		return $this->action_path . "?create_item&category_id=$category_id";
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
				'node_id' => $this->node_id,
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
	 * Получить форму выбора экземпляра каталога.
	 *
	 * @param bool $get_tpl_path - получить путь к шаблону.
	 * @return array|string
	 */
	public function getCreateEntityForm($get_tpl_path = false)
	{
		if ($get_tpl_path) {
			return dirname(__FILE__) . '/CreateEntityForm.tpl';
		}
		
		$form_data = array(
			'action' => $this->Env->current_folder_path,
			'target' => '_parent',
			'enctype' => 'multipart/form-data',
			'hiddens' => array(
				'node_id' => $this->node_id,
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
		return $form_data;
	}

	/**
	 * Получить данные формы создания категории.
	 *
	 * @param
	 * @return
	 */
	public function getCreateCategoryFormData()
	{
		$tmp = array();
		$tmp[1] = '[Корневая категория]';
		
		foreach ($this->getCategoriesList(1) as $key => $value) {
			$pfx = ' .. ';
			while($value['level']--) {
				$pfx .= ' .. ';
			}
			$tmp[$key] = $pfx . $value['title'];
		}
		
		$form_data = array(
			'action' => $this->action_path,
			'hiddens' => array(
				'node_id' => $this->node_id,
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
					'value' => 1,
					'options' => $tmp,
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
		/*
		$form_data['fieldsets'] = array(
			'new_category' => array(
				'title' => 'Добавить новую категорию',
				'elements' => 'all',
				),
			);
		*/
		return $form_data;
	}
	
	/**
	 * Получить данные формы создания новой записи.
	 *
	 * @param string $path - путь в формате "monitor/lcd/".
	 * @return array
	 */
	public function getCreateItemFormData($path)
	{
		// Вычисление $category_id
		if (strlen($path) == 0) {
			$category_id = 1;
		} else {
			$tmp = $this->parser($path);
			$category_id = $tmp['data']['category_id'];
			unset($tmp);
		}
		
		$images = array();	
		$prototype = $this->getItemPrototype($category_id);
		
		$Editor = new Component_Editor();
		$DatePicker = new Component_DatePicker();
		
		$categories_list = array();
		$categories_list[1] = '[Корневая категория]';
		foreach ($this->getCategoriesList(1) as $key => $value) {
			$pfx = '';
			while($value['level']--) {
				$pfx .= ' .. ';
			}
			$categories_list[$key] = $pfx . $value['title'];
		}
		
		$form_data = array(
			'action' => $this->Env->current_folder_path,
			'target' => '_parent',
			'enctype' => 'multipart/form-data',
			'hiddens' => array(
				'node_id' => $this->node_id,
				'pd[category_id]' => $category_id,
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
					'value' => '',
					),
				'pd[category_id]' => array(
					'label' => 'Категория',
					'type' => 'select',
					'value' => $category_id,
					'options' => $categories_list,
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
				'submit[create_item]' => array(
					'type' => 'submit',
					'value' => 'Добавить запись',
					),
				
				),
			'autofocus' => 'pd[uri_part]',
			);

		// Прототип записи.
		$Date = new Helper_Date();
		
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
				
				switch ($value['type']) {
					case 'file':
					case 'img':
					case 'image':
						$form_data['elements'][$value['name']] = array (
							'label' => $value['title'],
							'type' => 'file',
							);
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
							//'value' => $value['value'],
							);
						break;
					case 'datetime':
						$form_data['elements']['pd[content][' . $key . ']'] = array (
							'label' => $value['title'],
							'type' => 'string',
							'readonly' => false,
							'onfocus' => $DatePicker->onfocus(),
							'value' => $Date->getDatetime(),
							);
						break;
					case 'select':
						$options_list = explode(';', $value['params']);
						if ($value['is_required'] == 0) {
							$options[''] = '';
						}
						foreach ($options_list as $option_value) {
							$options[trim($option_value)] = trim($option_value);
						}
						
						$form_data['elements']['pd[content][' . $key . ']'] = array (
							'label' => $value['title'],
							'type' => 'select',
							//'value' => $value['value'],
							'options' => $options,
							);
						break;
					default;
				}
			}
		}
		
		// Тэги.
		// @todo сделать через методы юниката!
		$sql = "SELECT * 
			FROM {$this->prefix}tags AS t,
				 {$this->prefix}tags_translation AS tt
			WHERE t.entity_id = '{$this->entity_id}'
			AND tt.entity_id = '{$this->entity_id}'
			AND t.site_id = '{$this->Env->site_id}'
			AND tt.site_id = '{$this->Env->site_id}'
			AND t.tag_id = tt.tag_id
			";
		$result = $this->DB->query($sql);
		$form_fields_tags_list = array();
		while ($row = $result->fetchObject()) {
			$form_data['elements']['pd[tags][' . $row->tag_id . ']'] = array (
				'label' => $row->title,
				'type' => 'checkbox',
				'value' => 0,
				);
			$form_fields_tags_list[] = "pd[tags][$row->tag_id]";
		}
		
		// Филдсеты.
		$form_data['fieldsets'] = array(
			'system_item_properties' => array(
				'title' => 'Системные параметры',
				'elements' => array(
					'pd[is_active]',
					'pd[uri_part]',
					'pd[category_id]',
					'pd[meta][keywords]',
					'pd[meta][description]',					
				),
			),
			'item_properties' => array(
				'title' => 'Свойства',
				'elements' => $form_fields_list,
			),
			'tags' => array(
				'title' => 'Тэги',
				'elements' => $form_fields_tags_list,
				),
			);
			
		if (count($categories_list) === 1) {
			unset($form_data['elements']['pd[category_id]']);
			unset($form_data['fieldsets']['system_item_properties']['elements'][2]);
		}
		
		return $form_data;
	}

	/**
	 * Получить данные формы создания тэга в группе.
	 *
	 * @param int $tags_group_id - ид группы тэгов.
	 * @return array
	 */
	public function getCreateTagFormData($tags_group_id)
	{
		$form_data = array(
			'action' => $this->action_path,
			'hiddens' => array(
				'node_id' => $this->node_id,
				'pd[tags_group_id]' => $tags_group_id,
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
				'submit[create_tag]' => array(
					'type' => 'submit',
					'value' => 'Создать тэг',
					),
				),
			'autofocus' => 'pd[title]',
			);
		return $form_data;
	}
	
	/**
	 * Получить данные формы создания свойства в группе.
	 *
	 * @param
	 * @return
	 */
	public function getCreatePropertyFormData($properties_group_id)
	{
		$form_data = array(
			'action' => $this->action_path,
			'hiddens' => array(
				'node_id' => $this->node_id,
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
		return $form_data;
	}
	
	/**
	 * Получить данные формы создания новой группы свойств.
	 *
	 * @param void
	 * @return array
	 */
	public function getCreatePropertiesGroupFormData()
	{
		$form_data = array(
			'action' => $this->action_path,
			'hiddens' => array(
				'node_id' => $this->node_id,
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
		return $form_data;
	}
	
	/**
	 * Получить данные формы создания новой группы тэгов.
	 *
	 * @param void
	 * @return array
	 */
	public function getCreateTagsGroupFormData()
	{
		$form_data = array(
			'action' => $this->action_path,
			'hiddens' => array(
				'node_id' => $this->node_id,
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
				'submit[create_tags_group]' => array(
					'type' => 'submit',
					'value' => 'Добавить группу тэгов', // @todo 
					),
				),
			'autofocus' => 'pd[title]',
			);
		return $form_data;
	}
	
	/**
	 * Получить данные формы редактирования категории.
	 *
	 * @param
	 * @return
	 */
	public function getEditCategoryFormData($category_id)
	{
		$data = $this->getCategoryData($category_id);

		$tmp = array();
		$tmp[1] = '[Корневая категория]';
		
		foreach ($this->getCategoriesList(1) as $key => $value) {
			$pfx = ' .. ';
			while($value['level']--) {
				$pfx .= ' .. ';
			}
			$tmp[$key] = $pfx . $value['title'];
		}
		
		if (count($this->getCategoryInheritanceList($category_id)) == 0 and $this->getItemsCount(array('categories' => $category_id)) == 0) {
			$disabled = false;
			$delete_category_msg = '';
		} else {
			$disabled = true;
			$delete_category_msg = ' (можно только пустую категорию)';
		}
		
		$form_data = array(
			'action' => $this->action_path,
			'hiddens' => array(
				'node_id' => $this->node_id,
				'pd[category_id]' => $data['category_id'],
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
					'options' => $tmp,
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
		return $form_data;
	}

	/**
	 * Получить данные формы редактирования записи.
	 *
	 * @param string $path - путь в формате "monitor/lcd/".
	 * @return array
	 */
	public function getEditItemFormData($path)
	{
		if (isset($_GET['edit_item']) and is_numeric($_GET['edit_item'])) {
			$item_id = $_GET['edit_item'];
		} else {
			return false;
		}

		if (strlen($path) == 0) {
			$category_id = 1;
		} else {
			$tmp = $this->parser($path);
			$category_id = $tmp['data']['category_id'];
			unset($tmp);
		}
		
		$item = $this->getItem($item_id, array('categories' => 1));
		$current_category_id = 1;
		if (isset($item['categories'])) {
			foreach ($item['categories'] as $key => $value) {
				$current_category_id = $key;
				break;
			}
		}

		$images = array();	
		$prototype = $this->getItemPrototype($current_category_id);

		$Editor = new Component_Editor();
		$DatePicker = new Component_DatePicker();
		
		$categories_list = array();
		$categories_list[1] = '[Корневая категория]';
		foreach ($this->getCategoriesList(1) as $key => $value) {
			$pfx = '';
			while($value['level']--) {
				$pfx .= ' .. ';
			}
			$categories_list[$key] = $pfx . $value['title'];
		}
		
		$form_data = array(
			'action' => $this->Env->current_folder_path,
			'target' => '_parent',
			'enctype' => 'multipart/form-data',
			'hiddens' => array(
				'node_id' => $this->node_id,
				'pd[category_id]' => $category_id,
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
				'pd[category_id]' => array(
					'label' => 'Категория',
					'type' => 'select',
					'value' => $current_category_id,
					'options' => $categories_list,
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
			'autofocus' => 'pd[uri_part]',
			);

		// Для начала создаётся массив с полями формы, который берется из прототипа записи. 
		// Затем на него накладываются существующие свойства записи.
		$form_fields = array();
		foreach ($prototype as $properties_group => $value1) {
			foreach ($value1['properties'] as $key => $value) {
				$form_fields[$value['name']] = array(
					'property_id'	=> (string) $key,
					'type'			=> $value['type'],
					'title'			=> $value['title'],
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
					array_pop($form_fields_list);
					$form_fields_list[] = $key;
					break;
				case 'select':
					$options = array();
					$options_list = explode(';', $value['params']);
					if ($value['is_required'] == 0) {
						$options[''] = '';
					}
					foreach ($options_list as $option_value) {
						$options[trim($option_value)] = trim($option_value);
					}
					
					$form_data['elements']['pd[content][' . $value['property_id'] . ']'] = array (
						'label' => $value['title'],
						'type' => 'select',
						'value' => $value['value'],
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
						'readonly' => false, // true
						'onfocus' => $DatePicker->onfocus(),
						'value' => $value['value'],
						);
					break;
				default;
			}
		}
		
		// Тэги.
		// @todo сделать через методы юниката!
		$sql = "SELECT * 
			FROM {$this->prefix}tags AS t,
				 {$this->prefix}tags_translation AS tt
			WHERE t.entity_id = '{$this->entity_id}'
			AND tt.entity_id = '{$this->entity_id}'
			AND t.site_id = '{$this->Env->site_id}'
			AND tt.site_id = '{$this->Env->site_id}'
			AND t.tag_id = tt.tag_id
			";
		$result = $this->DB->query($sql);
		$form_fields_tags_list = array();
		while ($row = $result->fetchObject()) {
			$form_data['elements']['pd[tags][' . $row->tag_id . ']'] = array (
				'label' => $row->title,
				'type' => 'checkbox',
				'value' => 0,
				);
			$form_fields_tags_list[] = "pd[tags][$row->tag_id]";
		}
		
		$sql = "SELECT * 
			FROM {$this->prefix}tags_items_relation
			WHERE entity_id = '{$this->entity_id}'
			AND item_id = '$item_id'
			AND site_id = '{$this->Env->site_id}'
			";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$form_data['elements']['pd[tags][' . $row->tag_id . ']']['value'] = 1;
		}

		// Филдсеты.
		$form_data['fieldsets'] = array(
			'system_item_properties' => array(
				'title' => 'Системные параметры',
				'elements' => array(
					'pd[is_active]',
					'pd[create_datetime]',
					'pd[uri_part]',
					'pd[category_id]',
					'pd[meta][keywords]',
					'pd[meta][description]',
				),
			),
			'item_properties' => array(
				'title' => 'Свойства',
				'elements' => $form_fields_list,
			),
			'tags' => array(
				'title' => 'Тэги',
				'elements' => $form_fields_tags_list,
				),
			);

		if (isset($_GET['admin'])) {
			unset($form_data['target']);
			$form_data['hiddens']['return_to'] = $this->action_path . '?items';
		}
			
		if (count($categories_list) === 1) {
			unset($form_data['elements']['pd[category_id]']);
			unset($form_data['fieldsets']['system_item_properties']['elements'][3]);
		}
		
		return $form_data;
	}
	
	/**
	 * Получить данные формы редактирования тэга.
	 *
	 * @param int $tag_id
	 * @return array
	 */
	public function getEditTagFormData($tag_id)
	{
		$tmp = $this->getTagsList();
		$tag = $tmp[$tag_id];
		
		$form_data = array(
			'action' => $this->action_path,
			'hiddens' => array(
				'node_id' => $this->node_id,
				'pd[tag_id]' => $tag_id,
				),
			'elements' => array(
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'string',
					'value' => $tag['title'],
					),
				'pd[name]' => array(
					'label' => 'Техническое имя',
					'type' => 'string',
					'value' => $tag['name'],
					),
				'pd[pos]' => array(
					'label' => 'Позиция',
					'type' => 'string',
					'value' => $tag['pos'],
					),
				'pd[tags_group_id]' => array(
					'label' => 'Группа тэгов',
					'type' => 'string',
					'value' => $tag['tags_group_id'],
					),
				),
			'buttons' => array(
				'submit[update_tag]' => array(
					'type' => 'submit',
					'value' => 'Сохранить тэг',
					),
				'submit[cancel]' => array(
					'type' => 'submit',
					'value' => 'Отменить',
					'onclick' => 'history.back(); return false;',
					),
				),
			'autofocus' => 'pd[title]',
			);
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
		
		if (isset($property['items_count']) and (int)$property['items_count'] === 0) {
			$delete_disabled = false;
		} else {
			$delete_disabled = true;
		}
		
		$form_data = array(
			'action' => $this->action_path,
			'hiddens' => array(
				'node_id' => $this->node_id,
				'pd[property_id]' => $property_id,
				),
			'elements' => array(
				'pd[is_active]' => array(
					'label' => 'Включено?',
					'type' => 'checkbox',
					'value' => $property['is_active'],
					),
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'string',
					'value' => $property['title'],
					),
				'pd[name]' => array(
					'label' => 'Техническое имя',
					'type' => 'string',
					'value' => $property['name'],
					'disabled' => true,
					),
				'pd[pos]' => array(
					'label' => 'Позиция',
					'type' => 'string',
					'value' => $property['pos'],
					),
				'pd[properties_group_id]' => array(
					'label' => 'Группа свойств',
					'type' => 'string',
					'value' => $property['properties_group_id'],
					),
				'pd[params]' => array(
					'label' => 'Параметры',
					'type' => 'string',
					'value' => $property['params'],
					),
				'pd[type]' => array(
					'label' => 'Тип',
					'type' => 'string',
					'value' => $property['type'],
					'disabled' => true,
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
				),
			'buttons' => array(
				'submit[update_property]' => array(
					'type' => 'submit',
					'value' => 'Сохранить свойство', // @todo 
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
		return $form_data;
	}
	
	/**
	 * Получить данные формы редактирования группы тэгов.
	 *
	 * @param int $tags_group_id
	 * @return array
	 */
	public function getEditTagsGroupFormData($tags_group_id)
	{
		$data = $this->getTagsGroupsList($tags_group_id);
		$form_data = array(
			'action' => $this->action_path,
			'hiddens' => array(
				'node_id' => $this->node_id,
				'pd[tags_group_id]' => $tags_group_id,
				),
			'elements' => array(
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'string',
					'value' => $data[$tags_group_id]['title'],
					),
				'pd[name]' => array(
					'label' => 'Техническое имя',
					'type' => 'string',
					'value' => $data[$tags_group_id]['name'],
					),
				'pd[pos]' => array(
					'label' => 'Позиция',
					'type' => 'string',
					'value' => $data[$tags_group_id]['pos'],
					),
				),
			'buttons' => array(
				'submit[update_tags_group]' => array(
					'type' => 'submit',
					'value' => 'Сохранить изменения',
					),
				),
			'autofocus' => 'pd[title]',
			);
		return $form_data;
	}
	
	/**
	 * Получить данные формы редактирования группы свойств.
	 *
	 * @param int $properties_group_id
	 * @return array
	 */
	public function getEditPropertiesGroupFormData($properties_group_id)
	{
		$categories_relation = '';
		$sql = "SELECT * 
			FROM {$this->prefix}properties_groups_category_relation
			WHERE site_id = '{$this->Env->site_id}'
			AND entity_id = '{$this->entity_id}'
			AND properties_group_id = '$properties_group_id'
			";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			if ($categories_relation === '') {
				$categories_relation .= $row->category_id;
			} else {
				$categories_relation .= ',' . $row->category_id;
			}
		}

		$data = $this->getPropertiesGroupsList($properties_group_id);
		$form_data = array(
			'action' => $this->action_path,
			'hiddens' => array(
				'node_id' => $this->node_id,
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
				'pd[categories_relation]' => array(
					'label' => 'Привязка к категориям',
					'type' => 'string',
					'value' => $categories_relation,
					),
				),
			'buttons' => array(
				'submit[update_properties_group]' => array(
					'type' => 'submit',
					'value' => 'Сохранить изменения',
					),
				),
			'autofocus' => 'pd[title]',
			);
		return $form_data;
	}
	
}
