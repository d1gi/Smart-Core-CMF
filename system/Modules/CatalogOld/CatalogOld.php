<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Базовый Модуль Каталога.
 * 
 * @uses Component_DatePicker
 * @uses Component_Editor
 * @uses Component_Unicat
 * @uses DB
 * @uses Permissions
 * 
 * @version 2011-11-01.0
 */
class Module_CatalogOld extends Module
{
	/**
	 * Экземпляр каталога.
	 * @var int
	 */
	protected $entity_id;
	
	/**
	 * Префикс таблиц юниката.
	 * @var string
	 */
	protected $unicat_db_prefix;
	
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
	 * Генерировать ссылки только для записей с непустым свойством.
	 * @var string
	 */
	protected $link_related_by_property;
	
	/**
	 * Использовать даты начала и окончания публикации?
	 * @var bool
	 */
	protected $use_publication_period;
	
	/**
	 * Объект компонента Unicat
	 * @var object
	 */
	protected $Unicat;
	protected $__unicat_params;
	
	/**
	 * Запоминаем данные парсера.
	 * @var mixed
	 */
	protected $parser_data;
	
	/**
	 * Конструктор.
	 */
	protected function init()
	{
		$this->Node->setDefaultParams(array(
			'items_per_page' => 10,
			'class_prefix'	 => 'cat_',
			'entity_id'		 => 0,
			'media_collection_id' => 0,
			'unicat_db_prefix' => 'unicat_',
			));

		$this->entity_id				= $this->Node->getParam('entity_id');
		$this->class_prefix				= $this->Node->getParam('class_prefix');
		$this->media_collection_id		= $this->Node->getParam('media_collection_id');
		$this->items_per_page 			= $this->Node->getParam('items_per_page');
		//$this->link_related_by_property	= $this->Node->params['link_related_by_property'];
		$this->unicat_db_prefix 		= $this->Node->getParam('unicat_db_prefix');
		
		$this->__unicat_params = array(
			'entity_id' => $this->entity_id,
			//'node_id' => $this->Node->id,
			'node' => &$this->Node,
			'db_connection' => $this->DB,
			'media_collection_id' => $this->media_collection_id,
			'unicat_db_prefix' => $this->unicat_db_prefix,
			);
		
		$this->Unicat = new Component_UnicatOld($this->__unicat_params);
	}
	
	/**
	 * Запуск модуля.
	 * 
	 * @return void
	 */
	public function run($parser_data)
	{
		// Опции выборки записей.
		$options = array(
			'is_active' => 1,
			//'is_deleted' => 1,
			/*			
			'order' => array(
				'datetime'=> 'DESC', 
				'title'=> 'ASC', 
				),
			/*
			'filters' => array(
				array(
					'property' => 'datetime',
					'comparison' => '<=',
					'value' => '2011-06-10 00:00:00',
					),
				array(
					'property' => 'datetime',
					'comparison' => '>=',
					'value' => '2011-06-10 00:00:00',
					),
				array('datetime', '>=', '2011-06-10 00:00:00'),
				),
			*/
			);
			
		$this->_catalogRun($parser_data, $options);
	}
	
	/**
	 * Стандартный запуск модуля.
	 *
	 * @param mixed $parser_data
	 * @param array $user_options
	 * @return void
	 */
	protected function _catalogRun($parser_data, $user_options)
	{
		$this->parser_data = $parser_data;
		
		// Если экземпляр не установлен, пледлагается создать новый.
		if ($this->entity_id == 0) {
			if ($this->Permissions->isRoot()) {
				$this->output_data['message'] = 'Set correct unicat entity ID.';
				$this->output_data['create_entity'] = $this->Unicat->getCreateEntityForm();
			}
			return;
		}

		if (count($parser_data) > 0) {
			$category_id = $parser_data['category_id'];
			if (isset($parser_data['page'])) {
				$page_num = $parser_data['page'];
			}
		} else {
			$category_id = 1;
			$page_num = 1;
		}
		
		// Префикс CSS классов
		$this->output_data['class_prefix'] = $this->class_prefix;
		
		// Выбрана запись. Парсер вернул ID записи, которае находится в массиве $parser_data.
		if (isset($parser_data['item_id']) and is_numeric($parser_data['item_id'])) {
			$this->setTpl('Item');
			$this->output_data['item'] = $this->Unicat->getItem($parser_data['item_id'], array(
				'show_in_view' => 1,
				));
			// Мета-тэги
			if (!empty($this->output_data['item']['meta'])) {
				foreach ($this->output_data['item']['meta'] as $key => $value) {
					$this->EE->addHeadMeta($key, $value);
				}
			}
			$this->output_data['categories'] = "@todo принадледность к разделам";
		}
		// Запись не выбрана - генерируется список записей.
		else { 
			// @todo сделать нормальные права
			/*
			if ($this->Permissions->isAdmin() or $this->Permissions->isRoot()) {
				
			}
			*/
			
			// Мета-тэги
			if (!empty($parser_data['meta'])) {
				foreach ($parser_data['meta'] as $key => $value) {
					$this->EE->addHeadMeta($key, $value);
				}
			}
			
			// OPTIONS!
			$options = array(
				'categories' => $category_id,
				'paginator' => array(
					'items_per_page' => $this->items_per_page,
					'current_page' => $page_num,
					),
				'link' => array(
					'postfix' => '.html',
					'prefix' => '@todo module_path',
					//'related_by_property' => $this->link_related_by_property,				
					),
				);
			foreach ($user_options as $key => $value) {
				$options[$key] = $value;
			}

			$this->output_data['items'] = $this->Unicat->getItems($options);
			$this->output_data['tags'] = '@todo облако тегов, в случае если в опциях запрошено получить облако тэгов.';
			
			// Постраничность 
			$this->output_data['pages'] = new Helper_Paginator(array(
					'items_count' => $this->Unicat->getItemsCount($options),
					'items_per_page' => $this->items_per_page,
					'current_page' => $page_num,
					'link_tpl' => $this->Unicat->getUriByCategoryId($category_id) . 'page_{PAGE}/',
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
			case 'getCategoriesTree':
				if (empty($args)) {
					return $this->Unicat->getCategoriesList(1);
				} else {
					return $this->Unicat->getCategoriesList($args['category_id']);
				}
				break;
			case 'getTagsCloud':
				return $this->Unicat->getTagsList();
				break;
			case 'getTagData':
				return $this->Unicat->getTagData($args['tag_id']);
				break;
			case 'getTagId':
				return $this->Unicat->getTagId($args['name']);
				break;
			case 'getItemsByTag':
				/*$options = array(
					'tag' => $args['tag'],
					'paginator' => array(
						'items_per_page' => $args['items_per_page'],
						'current_page' => $args['current_page'],
						),
					);
				*/
				return $this->Unicat->getItems($args['options']);
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
				if (isset($item_id['data']['item_id'])) {
					return $item_id['data']['item_id'];
				} else {
					return false;
				}
				break;
			case 'getUriByCategoryId':
				return $this->Unicat->getUriByCategoryId($args['category_id']);
				break;
			default;
		}
		return null;
	}
	
	/**
	 * Парсер части УРИ
	 * 
	 * @param string $path
	 * @return array
	 */
	public function parser($path)
	{
		return $this->Unicat->parser($path);
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
