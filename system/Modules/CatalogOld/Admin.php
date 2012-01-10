<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс с административными методами.
 * 
 * @version 2011-09-08.0
 */
class Module_CatalogOld_Admin extends Module_CatalogOld implements Admin_ModuleInterface
{

	/**
	 * Конструктор.
	 */
	protected function init()
	{
		parent::init();
		$this->Unicat = new Component_UnicatOld_Admin($this->__unicat_params);
	}

	/**
	* Функция обработки дейсвий над нодой.
	* 
	* @param string $params - часть адреса идущая после ключевого слова "action" в строке запроса.
	* @return void
	*/
	public function nodeAction($params)
	{
		$this->output_data = $this->Unicat->action($params);
		$this->setTpl($this->Unicat->getTpl());
		return true;
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
		if (isset($this->output_data['item'])) {
			$this->default_action = 'edit';
			$front_controls['edit'] = array(
				'popup_window_title' => 'Редактирование записи',
				'title' => 'Редактировать',
				'link' => $this->Unicat->getEditItemLink($this->output_data['item']['item_id']),
				'ico' => 'edit',
				);
		}
		// Действием по умолчанию для ноды является "Добавить запись"
		else if (isset($this->output_data['items'])){
			$this->default_action = 'create_item';
			$front_controls['add'] = array(
				'popup_window_title' => 'Добавить запись',
				'title' => 'Добавить запись',
				'link' => $this->Unicat->getCreateItemLink($this->Node->parser_data['path']),
				'ico' => 'edit',
				);
			$front_controls['manage_category'] = array(
				'popup_window_title' => 'Управление',
				'title' => 'Управление',
				'link' => $this->Unicat->getManageCategoriesLink(),
				'ico' => 'add',
				);
		}
		
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
		if (isset($this->output_data['items'])) {
			foreach ($this->output_data['items'] as $key => $value) {
				$frontend_inner_controls[$this->class_prefix . 'item_id_' . $key]['edit'] = array(
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
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		$entities_list = $this->Unicat->getEntitiesList();
		$entities_list[0] = '';
		ksort($entities_list);
		
		$node_params = array(
			'items_per_page' => array(
				'label' => 'Записей на страницу:',
				'type' => 'text',
				'value' => $this->Node->getParam('items_per_page'),
				),
			'class_prefix' => array(
				'label' => 'Префикс CSS классов:',
				'type' => 'text',
				'value' => $this->Node->getParam('class_prefix'),
				),
			'entity_id' => array(
				'label' => 'Экземпляр каталога:',
				'type' => 'select',
				'value' => $this->Node->getParam('entity_id'),
				'options' => $entities_list,
				),
			'media_collection_id' => array(
				'label' => 'Медиа коллекция:',
				'type' => 'text',
				'value' => $this->Node->getParam('media_collection_id'),
				),
			'unicat_db_prefix' => array(
				'label' => 'Префикс таблиц юниката:',
				'type' => 'text',
				'value' => $this->Node->getParam('unicat_db_prefix'),
				),
			);
		return $node_params;
	}

	/**
	 * Вызывается при создании ноды.
	 * 
	 * @return array $params
	 * @todo потестить на создание ноды и убрать.
	 *
	public function createNode()
	{
		$params = array(
			'items_per_page' => 10,
			'class_prefix'	 => 'cat_',
			'entity_id'		 => 0,
			'media_collection_id' => 0,
			'unicat_db_prefix' => 'unicat_',
			);
		return $params;
	}
	*/
}
