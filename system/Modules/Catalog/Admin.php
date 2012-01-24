<?php 
/**
 * Класс с административными методами.
 * 
 * @version 2012-01-25.0
 */
class Module_Catalog_Admin extends Module_Catalog implements Admin_ModuleInterface
{
	/**
	 * Конструктор.
	 */
	protected function init()
	{
		parent::init();
		$this->Unicat = new Component_Unicat_Admin($this->unicat_params);
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
		else if ($this->View->items){
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
		if ($this->View->items) {
			foreach ($this->View->items as $key => $value) {
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
		if (!isset($entities_list[0])) {
			$entities_list[0] = '[ Не выбран ]';
		}
		ksort($entities_list);
		
		$Media = new Component_Media();
		$media_collections = array('0' => '[ Не выбрана ]');
		$media_collections = array_merge($media_collections, $Media->getCollectionsList());
		
		return array(
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
				'type' => 'select',
				'options' => $media_collections,
				'value' => $this->Node->getParam('media_collection_id'),
				),
			'unicat_db_prefix' => array(
				'label' => 'Префикс таблиц юниката:',
				'type' => 'text',
				'value' => $this->Node->getParam('unicat_db_prefix'),
				),
			);
	}

	/**
	 * Вызывается при создании ноды.
	 * 
	 * @return array $params
	 * @todo потестить на создание ноды и убрать.
	 *
	public function createNode()
	{
		$this->Unicat->_systemCreateNode();
		$params = parent::createNode();
		return $params;
	}
	*/
}