<?php
/**
 * Базовый Модуль Каталога.
 * 
 * @uses Component_DatePicker
 * @uses Component_Unicat
 * @uses DB
 * @uses Permissions
 * 
 * @version 2012-01-14.0
 */
class Module_Catalog extends Module
{
	/**
	 * Кол-во записей на страницу.
	 * @var int
	 */
	protected $items_per_page;
	
	/**
	 * Префикс имени класса, стандартного дива.
	 * @var string
	 */
	protected $class_prefix;
	
	/**
	 * Объект компонента Unicat
	 * @var object
	 */
	protected $Unicat;
	protected $unicat_params;
	
	/**
	 * Обязательные свойства записей.
	 * @var array|false
	 */
	protected $unicat_requred_items_properties = false;

	/**
	 * Обязательные стуктуры.
	 * @var array|false
	 */
	protected $unicat_requred_structures = false;
	
	/**
	 * Конструктор.
	 */
	protected function init()
	{
		$this->Node->setDefaultParams(array(
			'entity_id'		 		=> 0,
			'class_prefix'	 		=> 'cat_',
			'media_collection_id'	=> 0,
			'items_per_page'		=> 15,
			'unicat_db_prefix'		=> 'unicat_',
			));

		$this->unicat_params = array(
			'entity_id'				=> $this->Node->getParam('entity_id'),
			'node'					=> &$this->Node,
			'db_connection'			=> $this->DB,
			'media_collection_id'	=> $this->Node->getParam('media_collection_id'),
			'unicat_db_prefix'		=> $this->Node->getParam('unicat_db_prefix'),
			'requred_structures'	=> $this->unicat_requred_structures,
			'requred_items_properties'	=> $this->unicat_requred_items_properties,
			);
		$this->Unicat = new Component_Unicat($this->unicat_params);
	}
	
	/**
	 * Запуск модуля.
	 */
	public function run($params)
	{
		// Опции выборки записей.
		$options = array(
			'is_active' => 1,
			);
		$this->_catalogRun($params, $options);
	}
	
	/**
	 * NewFunction
	 *
	 * @param
	 */
	public function showItem($params)
	{
		$this->View->setTpl('Item');
		$this->View->item = $this->Unicat->getItem($params['item_id'], array(
			'show_in_view' => 1,
			));
		// Мета-тэги
		if (!empty($this->View->item['meta'])) {
			foreach ($this->View->item['meta'] as $key => $value) {
				$this->EE->addHeadMeta($key, $value);
			}
		}
		$this->View->categories = "@todo принадлежность к разделам";
	}
	
	/**
	 * Стандартный запуск модуля.
	 *
	 * @param mixed $params
	 * @param array $user_options
	 * @return void
	 */
	protected function _catalogRun($params, $user_options = array())
	{
		// Если таблиц не существует, предлагается создать.
		// или если экземпляр не установлен, пледлагается создать новый.
		if ($this->Permissions->isRoot()) {
			if ($this->unicat_params['entity_id'] == 0) {
				$this->View->create_entity_form_data = $this->Unicat->getCreateEntityFormData();
				$this->View->setTpl($this->Unicat->getCreateEntityFormTemplate());
//cmf_dump("123");
			} else if ($this->Unicat->isTablesExist() == false) {
				$this->View->create_tables_form_data = $this->Unicat->getCreateTablesFormData();
				$this->View->setTpl($this->Unicat->getCreateTablesFormTemplate());
			}
		}
		
		if ($this->Unicat->isTablesExist() == false or $this->unicat_params['entity_id'] == 0) {
			return;
		}
		
		// Парсер может вернуть данные по выбранной категории и номер страницы, а также ид записи.
		if (count($params) > 0) {
			$structures = $params['structures'];
			//$category_id = $params['category_id'];
			if (isset($params['page'])) {
				$page_num = $params['page'];
			}
		} else {
			$structures = false;
			$page_num = 1;
		}
		
		if (cf_is_get('page') and is_numeric($_GET['page'])) {
			$page_num = $_GET['page'];
			
			if ($page_num > 1) {
				$this->Breadcrumbs->add($this->Env->current_folder_path, 'Страница № ' . $page_num);
			}
		}
		
		// Префикс CSS классов
		$this->View->class_prefix = $this->class_prefix;
		
		// Выбрана запись. Парсер вернул ID записи, которае находится в массиве $params.
		if (isset($params['item_id']) and is_numeric($params['item_id'])) {
			/* // @todo убрать т.к. тепеь запакован в методе showItem
			$this->View->setTpl('Item');
			$this->View->item = $this->Unicat->getItem($params['item_id'], array(
				'show_in_view' => 1,
				));
			// Мета-тэги
			if (!empty($this->View->item['meta'])) {
				foreach ($this->View->item['meta'] as $key => $value) {
					$this->EE->addHeadMeta($key, $value);
				}
			}
			$this->View->categories = "@todo принадлежность к разделам";
			*/
		}
		// Запись не выбрана - генерируется список записей.
		else { 
			// @todo сделать нормальные права
			/*
			if ($this->Permissions->isAdmin() or $this->Permissions->isRoot()) {
				
			}
			*/
			
			// Мета-тэги
			if (!empty($params['meta'])) {
				foreach ($params['meta'] as $key => $value) {
					$this->EE->addHeadMeta($key, $value);
				}
			}
			
			// OPTIONS!
			$options = array(
				'structure' => $structures,
				'paginator' => array(
					'items_per_page' => $this->items_per_page,
					//'items_per_page' => 0,
					'current_page' => $page_num,
					),
				'link' => array(
					'postfix' => '.html',
					//'related_by_property' => $this->link_related_by_property,				
					),
				);
			$options = $options + $user_options;
			$this->View->items = $this->Unicat->getItems($options);
			
			// Постраничность 
			$this->View->Pages = new Helper_Paginator(array(
					'items_count'	 => $this->Unicat->getItemsCount($options),
					'items_per_page' => $this->items_per_page,
					'current_page'	 => $page_num,
					//'link_tpl'	 => $this->Unicat->getUriByCategoryId($category_id) . 'page_{PAGE}/',
					'link_tpl'		 => '?page={PAGE}',
					)
				);
		}
	}
	
	/**
	 * Обработчик хуков.
	 *
	 * @param string $method - имя вызываемого метода.
	 * @param array $args - массив с аргументами.
	 */
	public function hook($method, $args = false)
	{
		switch ($method) {
			case 'createItem':
				return $this->Unicat->createItem($args);
				break;
			case 'getCategoriesTree':
				if (empty($args)) {
					return null;
				} else {
					$parser_node_data = Kernel::getParserNodeData();
//cmf_dump($parser_node_data);					
					return (empty($parser_node_data) or $args['use_parcer_node_data'] == 0)
						? $this->Unicat->getCategoriesTree($args['structure_id'], $args['category_id'], $args['max_depth'])
						: $this->Unicat->getCategoriesTree($args['structure_id'], $parser_node_data['data']['structures'][$args['structure_id']], $args['max_depth']);
				}
				break;
			case 'getCategoriesList':
				if (empty($args)) {
					return null;
				} else {
					$parser_node_data = Kernel::getParserNodeData();
					return (empty($parser_node_data) or $args['use_parcer_node_data'] == 0)
						? $this->Unicat->getCategoriesList($args['structure_id'], $args['category_id'])
						: $this->Unicat->getCategoriesList($args['structure_id'], $parser_node_data['data']['structures'][$args['structure_id']]);
				}
				break;
			case 'getItems':
				return $this->Unicat->getItems(@$args['options']);
				break;
			case 'getItem':
				return $this->Unicat->getItem($args['item_id'], @$args['options']);
				break;
			case 'getItemsCount':
				return $this->Unicat->getItemsCount($args['options']);
				break;
			case 'getUniqueId':
				$item_id = Kernel::getParserNodeData();
				return isset($item_id['data']['item_id']) ? $item_id['data']['item_id'] : null;
				break;
			case 'getUriByCategoryId':
				return $this->Unicat->getUriByCategoryId($args['category_id']);
				break;
			default;
		}
		return null;
	}
	
	/**
	 * Роутинг.
	 * 
	 * @param string $path
	 * @return array
	 */
	public function router($path)
	{
//		$route = $this->Unicat->router($path);
		//$route['controller'] = __CLASS__;
//		return $route;
		return $this->Unicat->router($path);
	}
	
	/**
	 * Обработчик POST данных.
	 * 
	 * @param int $pd
	 * @param string $submit
	 */
	public function postProcessor($pd, $submit)
	{
		$this->Unicat->postProcessor($pd, $submit);
	}
		
}