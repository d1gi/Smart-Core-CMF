<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Компонент: Универсальный каталог, предоставляющий модулям интерфейс работы с
 * каталогизированными данными.
 * 
 * @uses Component_Media
 * @uses DB
 * @uses Helper_Uri
 * @uses Kernel
 * @uses Node
 * @uses User
 * 
 * @version 2011-10-30.0
 */
//class Component_Unicat extends Component_Unicat_Admin
class Component_UnicatOld extends Base
{
	/**
	 * Префикс таблиц.
	 * @var string
	 */
	protected $prefix;
	
	/**
	 * Экземпляр каталога.
	 * @var int
	 */
	protected $entity_id;
	
	/**
	 * Node ID.
	 * @var array
	 */
	protected $node_id;
	
	/**
	 * Кол-во записей на одну страницу по умолчанию.
	 * 
	 * 0 - отобразить все записи.
	 * 
	 * @var int
	 */
	protected $items_per_page;
	
	/**
	 * Текущая страница.
	 * @var int
	 */
	protected $current_page;
	
	/**
	 * Медиа хранилище.
	 * @var object
	 */
	protected $Media;
	
	/**
	 * Вспомогательные переменные для работы с категориями.
	 */
	protected $_category_tree_level = 0;
	protected $_category_tree_list_arr = array();
	protected $_category_inheritance_list = array();

	/**
	 * Текущая рабочая категория.
	 * @todo возможно ненужна.
	 */
	protected $current_category_id = 1;
	
	/**
	 * Язык сайта по умолчанию.
	 * @var varchar(2)
	 */
	protected $default_language_id;
	
	/**
	 * Активный язык сайта.
	 * @var varchar(2)
	 */
	protected $language_id;
	
	/**
	 * Вспомогательная перемення, для быстрого формирования ссылки на действия.
	 * @var string
	 */
	protected $action_path;
	
	protected $Node;
	
	/**
	 * Конструктор.
	 * 
	 * Массив параметров должен содержать следующие значения:
	 *  - entity_id
	 *  - node_id
	 *  - db_connection
	 *  - media_collection_id
	 *  - unicat_db_prefix
	 * 
	 * @param array $params
	 */
	public function __construct(array $params)
	{
		parent::__construct();
		
		if (isset($params['db_connection']) and $params['db_connection'] !== false) {
			$this->DB = $params['db_connection'];
		}
		
		if (isset($params['unicat_db_prefix'])) {
			$this->prefix = $params['unicat_db_prefix'];
		} else {
			$this->prefix = '';
		}
		
		$this->Node					= $params['node'];
		$this->node_id				= $this->Node->id;
		$this->action_path			= $this->Env->current_folder_path . ACTION . '/' . $this->Node->id . '/';
		$this->db_prefix 			= ''; // @todo 
		$this->entity_id			= $params['entity_id'];
		
		$this->current_page			= 1;
		$this->items_per_page		= 10;
		
		$this->Media = new Component_Media($params['media_collection_id']);
	}
	
	/**
	 * Получить свойства категории.
	 *
	 * @param
	 * @return
	 */
	public function getCategoryProperties($category_id)
	{
		$properties = array();
		$sql = "SELECT name, value
			FROM {$this->prefix}categories_properties
			WHERE entity_id = {$this->entity_id}
			AND category_id = '$category_id'
			AND site_id = '{$this->Env->site_id}'
			ORDER BY pos
			";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$properties[$row->name] = $row->value;
		}
		return $properties;
	}
	
	/**
	 * Сделать плоский список категорий для формирования хтмл списков.
	 * 
	 * Рекурсия.
	 * 
	 * @param array $items
	 */
	protected function _buildCategoriesTree($items)
	{
		foreach ($items as $key => $value) {
			$this->_category_tree_list_arr[$key]['level'] = $this->_category_tree_level;
			$this->_category_tree_list_arr[$key]['uri'] = $value['uri'];
			$this->_category_tree_list_arr[$key]['title'] = $value['title'];
			$this->_category_tree_list_arr[$key]['pid'] = $value['pid'];
			$this->_category_tree_list_arr[$key]['pos'] = $value['pos'];
			$this->_category_tree_list_arr[$key]['is_active'] = $value['is_active'];
			$this->_category_tree_list_arr[$key]['uri_part'] = $value['uri_part'];
			$this->_category_tree_list_arr[$key]['selected'] = $value['selected'];

			if (count($value['items']) > 0) {
				$this->_category_tree_level++;
				$this->_buildCategoriesTree($value['items']);
			}
		}
		$this->_category_tree_level--;
	}
	
	/**
	 * Получить плоский список категорий для формирования хтмл списков.  
	 *
	 * @param int $parent_id
	 * @param int|false $max_depth - максимальная вложенность в уровнях, false - не учитывать глубину.
	 * @param 1|0|'all' $is_active - учитывать активность.
	 * 
	 * @return array
	 */
	public function getCategoriesList($parent_id, $max_depth = false, $is_active = 1)
	{
		$this->_buildCategoriesTree($this->getCategoriesTree($parent_id, $max_depth, $is_active));
		$this->_category_tree_level = 0;
		return $this->_category_tree_list_arr;
	}
	
	/**
	 * Получить деревовидную структуру категорий.
	 * 
	 * Рекурсия.
	 * 
	 * @uses Kernel
	 * 
	 * @param int $parent_id
	 * @param int $max_depth
	 * @return array
	 * 
	 * @todo мультиязычность!
	 */
	public function getCategoriesTree($parent_id, $max_depth = false, $is_active = 1)
	{
		$uri = parse_url($_SERVER['REQUEST_URI']);
		$current_path = $uri['path'];
		
		// Настройка флага is_active.
		if ($is_active === 0 or $is_active === false) {
			$sql_is_active = ' AND c.is_active = 0 ';
		} elseif ($is_active === 'all') {
			$sql_is_active = '';
		} else {
			$sql_is_active = ' AND c.is_active = 1 ';
		}
		
		$this->_category_tree_level++;
		$items = array();

		$sql = "SELECT
				c.*,
				ct.title
			FROM {$this->prefix}categories AS c,  
				{$this->prefix}categories_translation AS ct
			WHERE c.entity_id = '{$this->entity_id}'
				AND c.site_id = '{$this->Env->site_id}'
				AND ct.site_id = '{$this->Env->site_id}'
				AND ct.entity_id = '{$this->entity_id}'
				AND ct.category_id = c.category_id
				AND c.pid = '$parent_id'
				$sql_is_active
			ORDER BY c.pos ";
		$result = $this->DB->query($sql);
		while($row = $result->fetchObject()) {
			// копаем до указанной глубины.
			if ($max_depth != false and $max_depth < $this->_category_tree_level) {
				continue;
			}

			$uri = $this->getUriByCategoryId($row->category_id);
			if (strpos($current_path, $uri) === 0) {
				$selected = 1;
			} else {
				$selected = 0;
			}
			
			$items[$row->category_id] = array(
				'selected' => $selected,
				'uri' => $uri,
				'title' => $row->title,
				'uri_part' => $row->uri_part,
				'is_active' => $row->is_active,
				'pid' => $row->pid,
				'pos' => $row->pos,
				'items' => $this->getCategoriesTree($row->category_id, $max_depth, $is_active),
				);
		} // end while $row
		
		$this->_category_tree_level--;
		return $items;
	}
	
	/**
	 * Получить данные категории.
	 *
	 * @param int $category_id
	 * @return array
	 */
	public function getCategoryData($category_id)
	{
		$sql = "SELECT c.*, ct.title, ct.meta
			FROM {$this->prefix}categories AS c,  
				{$this->prefix}categories_translation AS ct
			WHERE c.entity_id = '{$this->entity_id}'
			AND ct.entity_id = '{$this->entity_id}'
			AND c.site_id = '{$this->Env->site_id}'
			AND ct.site_id = '{$this->Env->site_id}'
			AND ct.category_id = c.category_id
			AND ct.category_id = '$category_id'
			";
		if ((int)$category_id === 1) {
			$sql = "SELECT *, '' AS title, '' AS meta
				FROM {$this->prefix}categories AS c
				WHERE c.entity_id = '{$this->entity_id}'
				AND c.site_id = '{$this->Env->site_id}'
				AND c.category_id = '$category_id'
				";
		}

		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		
		$cat = array(
			'category_id' => $category_id,
			'is_active' => $row->is_active,
			'is_inheritance' => $row->is_inheritance,
			'uri_part' => $row->uri_part,
			'title' => $row->title,
			'pid' => $row->pid,
			'pos' => $row->pos,
			'meta' => unserialize($row->meta),
			);
		
		return $cat;
	}
	
	/**
	 * Получение списка наследуемых категорий.
	 *
	 * @param
	 * @return array 
	 */
	public function getCategoryInheritanceList($category_id)
	{
		$this->_category_inheritance_list = array();
		$this->_buildCategoryInheritanceList($category_id);
		return $this->_category_inheritance_list;
	}
	
	/**
	 * Вспомогательный метод рекурсивного формирования списка наследованных категорий.
	 *
	 * @param int $category_id
	 * @return void
	 */
	protected function _buildCategoryInheritanceList($category_id)
	{
		$sql = "SELECT is_inheritance
			FROM {$this->prefix}categories 
			WHERE entity_id = {$this->entity_id}
			AND category_id = '$category_id'
			AND site_id = '{$this->Env->site_id}'
			AND is_active = 1
			";
		$result = $this->DB->query($sql);
		if ($result->rowCount() == 0) {
			return;
		}
		
		$row = $result->fetchObject();
		// @todo PHP Notice:  Trying to get property of non-object in E:\localhost\SmartCore\system\Components\Unicat\Unicat.php on line 362
		if ($row->is_inheritance == 0) {
			return;
		}		

		$sql = "SELECT category_id 
			FROM {$this->prefix}categories 
			WHERE pid = '$category_id'
			AND entity_id = {$this->entity_id}
			AND site_id = '{$this->Env->site_id}'
			AND is_inheritance = 1
			AND is_active = 1
			";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$this->_category_inheritance_list[] = $row->category_id;
			$this->_buildCategoryInheritanceList($row->category_id);
		}
	}
	
	/**
	 * Получить полную ссылку на категорию. Вместе с путём к папке, куда подключен модуль.
	 *
	 * @param int $category_id
	 * @return string
	 * 
	 * @todo multilang
	 */
	public function getUriByCategoryId($category_id)
	{
		$uri_parts = array();
		$uri = '';
		
		while($category_id != 1) {
			$sql = "SELECT pid, uri_part
				FROM {$this->prefix}categories
				WHERE category_id = '$category_id'
				AND entity_id = '{$this->entity_id}'
				AND site_id = '{$this->Env->site_id}'
				LIMIT 1 ";
			$result = $this->DB->query($sql);
			$row = $result->fetchObject();
			
			$category_id = $row->pid;
			$uri_parts[] = $row->uri_part;		
		}
		
		$uri_parts = array_reverse($uri_parts);
		foreach ($uri_parts as $value) {
			$uri .= $value . '/';
		}
	
		$Node = new Node();
		// return $this->Env->current_folder_path . Site::getHttpLangPrefix() . $uri;
		return Folder::getUri($Node->getProperties($this->node_id, 'folder_id')) . $uri;
	}
	
	/**
	 * Получение списка записей в заданном разделе.
	 * 
	 * Массив $options может принимать следующие значения:
	 * 
	 * @param array $options
	 * @param bool $return_items_count
	 * @return array|int
	 */
	public function getItems(array $options = null, $return_items_count = false)
	{
		$items = array();
		$sql_joins = array();
		$sql_comparisons = '';
		$sql_orders = array();
		
		// Устанавливается $link_related_by_property_id.
		$link_related_by_property_id = false;
		if (isset($options['link']['related_by_property'])) {
			// Проверка, существует ли такое свойство.
			$sql = "SELECT property_id, name FROM {$this->prefix}properties
				WHERE name = '{$options['link']['related_by_property']}'
				AND entity_id = '{$this->entity_id}'
				AND site_id = '{$this->Env->site_id}'
				AND is_active = '1'
				";
			$result = $this->DB->query($sql);
			if ($result->rowCount() == 1) {
				$row = $result->fetchObject();
				$link_related_by_property_id = $row->name;
			} else {
				$link_related_by_property_id = false;
			}
		}
		
		// Устанавливается $link_postfix, по умолчанию ".html".
		if (isset($options['link']['postfix'])) {
			$link_postfix = $options['link']['postfix'];
		} else {
			$link_postfix = '.html';
		}
		
		// Настройка флага is_active.
		if (isset($options['is_active']) and $options['is_active'] == 1) {
			$is_active = ' AND i.is_active = 1 ';
		} elseif (isset($options['is_active']) and $options['is_active'] === 'all') {
			$is_active = '';
		} elseif (isset($options['is_active']) and $options['is_active'] == 0) {
			$is_active = ' AND i.is_active = 0 ';
		} else {
			$is_active = ' AND i.is_active = 1 ';
		}
		
		// Настройка флага is_deleted.
		if (isset($options['is_deleted']) and $options['is_deleted'] == 1) {
			$is_deleted = ' AND i.is_deleted = 1 ';
		} elseif (isset($options['is_deleted']) and $options['is_deleted'] === 'all') {
			$is_deleted = '';
		} elseif (isset($options['is_deleted']) and $options['is_deleted'] == 0) {
			$is_deleted = ' AND i.is_deleted = 0 ';
		} else {
			$is_deleted = ' AND i.is_deleted = 0 ';
		}
		
		// Создаётся фрагмент SQL запроса для выборки по категориям.
		if (!isset($options['categories']) or $options['categories'] === 'all') {
			$sql_categories = '';
		} else {
			$category_id = $options['categories']; // @todo врменный хак!!!!!!
			
			$tmp = $this->getCategoryInheritanceList($category_id);
			$sql_categories = " AND ( icr.category_id = '$category_id' ";
			if (count($tmp) > 0) {
				foreach ($tmp as $value) {
					$sql_categories .= " \nOR icr.category_id = '$value' ";
				}
			}
			$sql_categories .= ' ) ';
		}
		
		// Постраничность.
		// Устанавливается $current_page, по умолчанию $this->current_page.
		if (isset($options['paginator']['current_page']) and is_numeric($options['paginator']['current_page'])) {
			$current_page = $options['paginator']['current_page'];
		} else {
			$current_page = $this->current_page;
		}
		
		if (isset($options['paginator']['items_per_page']) and is_numeric($options['paginator']['items_per_page'])) {
			$this->items_per_page = $options['paginator']['items_per_page'];
		}
		
		if ($this->items_per_page == 0) {
			$sql_limit = '';
		} else {
			$start_item = ($current_page - 1) * $this->items_per_page;
			$sql_limit = " LIMIT $start_item, {$this->items_per_page} ";
		}
		
		// Тэги
		if (isset($options['tag'])) {
			$sql_joins["{$this->prefix}tags_items_relation"] = "LEFT JOIN {$this->prefix}tags_items_relation AS tir USING (item_id, entity_id, site_id)";
			$sql_comparisons .= " AND tir.tag_id = '$options[tag]' ";
		}

		// Фильтры.
		//$filtered_items = array();
		if (isset($options['filters']) and is_array($options['filters'])) {
			foreach ($options['filters'] as $key => $filter) {
				if (isset($filter['property'])) { // полный вариант записи.
					$property_name = $filter['property'];
					$comparison = $filter['comparison'];
					$value = $filter['value'];
				} else { // сокращенный вариант записи.
					$property_name = $filter[0];
					$comparison = $filter[1];
					$value = $filter[2];
				}
								
				//$sql_comparisons[] = " AND ip$property_name.value {$filter['comparison']} {$filter['value']} ";
				$sql_comparisons .= " AND ip$property_name.value $comparison '$value' \n";
				
				$sql_joins["{$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_$property_name"] = "LEFT JOIN {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_$property_name AS ip$property_name USING (item_id)";
			}
		}

		// Сортировка.
		//$sql_order_joins = '';
		$sql_order_by = '';
		if (isset($options['order']) and is_array($options['order']) and !empty($options['order']) and $return_items_count === false) {
			$sql_order_by = 'ORDER BY ';
			$sql_order_by_cnt = 0; // @todo убрать
			foreach ($options['order'] as $property_name => $direction) {
				// перед каждым новым полем сортировки надо запятую поставить ;)
				if ($sql_order_by_cnt++ > 0) {
					$sql_order_by .= ', ';
				}

				if ($property_name === 'i.item_id') {
					$sql_order_by .= "$property_name $direction";
				} else {
					$sql_joins["{$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_$property_name"] = "LEFT JOIN {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_$property_name AS ip$property_name USING (item_id)";
					$sql_order_by .= "ip$property_name.value $direction";
				}
				
				// $sql_orders["ip$property_name.value"] = $direction;
				// @todo убрать
				//$sql_order_joins .= "LEFT JOIN {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_$property_name AS ip$property_name USING (item_id) \n";
			}
			unset($sql_order_by_cnt); // @todo убрать
		}
		
		$sql_joins2 = '';
		foreach ($sql_joins as $value) {
			$sql_joins2 .= $value . "\n";
		}
		
		// Устанавливается $show_in_list.
		if (isset($options['show_in_list'])) {
			$show_in_list = $options['show_in_list'];
		} else {
			$show_in_list = 1;
		}
		
		// Устанавливается $show_in_view.
		if (isset($options['show_in_view'])) {
			$show_in_view = $options['show_in_view'];
		} else {
			$show_in_view = false;
		}
		
		// Если запрошен $return_items_count, то выполняется другой запрос в БД и возвращается только кол-во всех записей.
		if ($return_items_count) {
			$sql = "SELECT count(i.item_id) AS items_count
				FROM {$this->prefix}items_categories_relation AS icr
				LEFT JOIN {$this->prefix}items AS i USING (item_id, entity_id, site_id)
				$sql_joins2
				WHERE icr.entity_id = '{$this->entity_id}'
				AND icr.site_id = '{$this->Env->site_id}'
				$is_active
				$is_deleted
				$sql_categories
				$sql_comparisons
				";
			$result = $this->DB->query($sql);
			$row = $result->fetchObject();
			return $row->items_count;
		} 
		// Получить все записи, указанного раздела.
		else {
			// #$sql_order_joins
			$sql = "SELECT i.item_id, i.uri_part, icr.category_id, i.create_datetime, i.owner_id 
				FROM {$this->prefix}items_categories_relation AS icr
				LEFT JOIN {$this->prefix}items AS i USING (item_id, entity_id, site_id)
				$sql_joins2
				WHERE icr.entity_id = '{$this->entity_id}'
				AND icr.site_id = '{$this->Env->site_id}'
				$is_active
				$is_deleted
				$sql_categories
				$sql_comparisons
				$sql_order_by
				$sql_limit
				";
		}
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$item = array();
			// Определяем, надо ли генерировать ссылку на запись
			if ($link_related_by_property_id !== false) {
				// FROM items_s{$this->Env->site_id}_e{$this->entity_id}_{$row->name}
				$sql2 = "SELECT value
					FROM {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$link_related_by_property_id}
					WHERE item_id = '{$row->item_id}'
					";
				$result2 = $this->DB->query($sql2);
				if ($result2->rowCount() == 1) {
					$row2 = $result2->fetchObject();
					// Если свойство есть и его значение больше 0, то генерируем ссылку.
					if (strlen($row2->value) != 0) {
						$item['link'] = $this->getUriByCategoryId($row->category_id) . $row->uri_part . $link_postfix;
					}
				}
			} else { // По умолчанию ссылки генерируются на все записи.
				$item['link'] = $this->getUriByCategoryId($row->category_id) . $row->uri_part . $link_postfix;
			}
						
//			$item['tags'] = $this->getItemTagsList($row->item_id);
			$item['properties'] = $this->getItem($row->item_id, array(
				'show_in_list' => $show_in_list,
				'show_in_view' => $show_in_view,
				));
				
			$items[$row->item_id] = $item;
		} // while ($row = $result->fetchObject())
		
		return $items;
	}
	
	/**
	 * Получить общее кол-во записей.
	 *
	 * @param array $options
	 * @return int - кол-во записей.
	 */
	public function getItemsCount(array $options = null)
	{
		return $this->getItems($options, true);
	}

	/**
	 * Получить список тегов записи.
	 *
	 * @param int $item_id
	 * @return array
	 */
	public function getItemTagsList($item_id)
	{
		$tags = array();
		$sql = "SELECT *
			FROM {$this->prefix}tags_items_relation AS tir,
				 {$this->prefix}tags AS t,
				 {$this->prefix}tags_translation AS tt
			WHERE tir.item_id = '$item_id'
			AND t.entity_id = {$this->entity_id}
			AND tir.entity_id = t.entity_id
			AND tt.entity_id = t.entity_id
			AND t.tag_id = tt.tag_id
			AND t.tag_id = tir.tag_id
			AND t.site_id = {$this->Env->site_id}
			AND tir.site_id = t.site_id
			AND tt.site_id = t.site_id
			";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$tags[$row->tag_id] = array(
				'name' => $row->name,
				'title' => $row->title,
//				'link' => $this->getUriByCategoryId(1) . 'tag/' . $row->name . '/',
				'link' => HTTP_ROOT . 'tag/' . $row->name . '/',
				);
		}
		
		return $tags;
	}
	
	/**
	 * Проверка записи на доступность.
	 * 
	 * Передаётся значение либо число, тогда поиск идёт по ID, а если строка, то по имени (uri_part)
	 * 
	 * @param int|string $var
	 * @return int|false
	 * 
	 * @todo видимо неправильно т.к. uri_part может быть числом, хотя можно запрашивать строку состоящую из цифр.
	 * @todo Указывание на ид категории в которой производить поиск.
	 * @todo решить как поступать с флагом is_active, может быть передавать параметры в массиве?
	 */
	public function isItemExist($var, $caterory_id = 1)
	{
		if (is_int($var)) {
			$sql = "SELECT * FROM {$this->prefix}items
				WHERE item_id = '$var'
				AND entity_id = '{$this->entity_id}'
				AND site_id = '{$this->Env->site_id}'
				AND is_active = '1'
				";
		} else if (is_string($var)) {
			$sql = "SELECT * FROM {$this->prefix}items
				WHERE uri_part = '$var'
				AND entity_id = '{$this->entity_id}'
				AND site_id = '{$this->Env->site_id}'
				AND is_active = '1'
				";
		}
		
		$result = $this->DB->query($sql);
		if ($result->rowCount() == 1) {
			$row = $result->fetchObject();
			return $row->item_id;
		} else {
			return false;
		}
	}
	
	/**
	 * Парсер строки запроса.
	 * 
	 * Пока что будет считаться, что записи имеют окончание .html в будущем надо будет предусмотреть
	 * возможность настройки этого момента например можно устанавливай другой формат окончания части
	 * URI, а также 
	 * 
	 * @param string $path - часть URI запроса
	 * @return array|false
	 * 
	 * @todo мультиязычность.
	 */
	public function parser($path)
	{
		$path_parts = explode('/', $path);

		// Если запрос пустой, возвращается false.
		if (isset($path_parts[0]) === false ) {
			return false;
		}
		
		$data = false;
		$breadcrumbs = array();
		$category_pid = 1;
		$page = 1;
		$item_id = false;
		$uri = '';
		$is_success = false;
		
		// Обработчик категорий и итемов.
		foreach ($path_parts as $key => $value) {
			// @todo прокомментировать!
			if($value == '' and $key != 0) { 
				break;
			}
			
			// Сначала проверяется, является ли часть запроса ссылкой на "запись" каталога (пока окончание на .html)
			if (($pos = strpos($value, '.html')) !== false and $pos + 5 == strlen($value)) {
				// Существует ли запись.
				if ($item_id = $this->isItemExist(basename($value, '.html'), $category_pid)) {
					$breadcrumbs[] = array(
						'uri'	=> $this->Env->current_folder_path . Site::getHttpLangPrefix() . $uri . $value,
						'title' => $this->getItemValue($item_id, 'title'),
						'descr' => '', // @todo сделать :) хотя непонятно пока из чего они могут браться...
						);
					$data = array(
						'data' => array(
							'path' => $path,
							'category_id' => $category_pid,
							'item_id' => $item_id,
							),
						'breadcrumbs' => $breadcrumbs,
						'title' => '' // @todo возможно не нужно т.к. вся инфа теперь в $breadcrumbs.
						);
					return $data;
				} else {
					// Ничего не найдено.
					return false;
				}
			} 
			
			// Определение запрошенной страницы.
			if (substr($value, 0, 5) === 'page_' and is_numeric(substr($value, 5)) ) {
				$page = substr($value, 5);
				$is_success = true;
				if ($page > 1) {
					$breadcrumbs[] = array (
						'uri'	=> $this->Env->current_folder_path . Site::getHttpLangPrefix() . $uri,
						'title' => 'Страница № ' . $page,
						'descr' => '', // @todo сделать :) хотя непонятно пока из чего они могут браться...
						);
				}
				$meta = null;
			} 
			// Если часть запроса не относится к "записи", то пробуем обработать его как "категорию".
			else {
				$sql = "
					SELECT *
					FROM {$this->prefix}categories AS c,
						 {$this->prefix}categories_translation AS ct
					WHERE c.entity_id = '{$this->entity_id}'
					AND ct.entity_id = '{$this->entity_id}'
					AND c.site_id = '{$this->Env->site_id}'
					AND ct.site_id = '{$this->Env->site_id}'
					AND c.category_id = ct.category_id
					AND c.pid = '$category_pid'
					AND c.uri_part = '$value'
					AND c.is_active = '1'
					AND ct.language_id = '{$this->Env->language_id}'
					";
				$result = $this->DB->query($sql);
				if ($result->rowCount() == 1) {
					$row = $result->fetchObject();
					$uri .= $value . '/';
					$breadcrumbs[] = array (
						'uri'	=> $this->Env->current_folder_path . Site::getHttpLangPrefix() . $uri,
						'title' => $row->title,
						'descr' => '', // @todo сделать :) хотя непонятно пока из чего они могут браться...
						);
					$category_pid = $row->category_id;
					$meta = unserialize($row->meta);
					$is_success = true;
				} else {
					$is_success = false;
				}
			}

		} // __end foreach $path_parts
		
		if ($is_success) {
			$data = array(
				'data' => array(
					'path'			=> $path,
					'category_id'	=> $category_pid,
					'page' 			=> $page, // @todo проверка существует ли запрошенная страница.
					'item_id'		=> $item_id,
					'meta'			=> $meta,
					),
				'breadcrumbs' 		=> $breadcrumbs,
				'title' 			=> '' // @todo возможно не нужно т.к. вся инфа теперь в $breadcrumbs.
				);
		}
		
		return $data;
	}
	
	/**
	 * Получение данных заданной записи.
	 * 
	 * Массив $options может принимать следующие значения:	
	 * - show_in_list - включать свойства предназначенные для просмотра в списке записей. (по умолчанию неучитывается)
	 * - show_in_view - включать свойства предназначенные для просмотра отдельной записи. (по умолчанию неучитывается)
	 * 
	 * @param int $item_id
	 * @param array $options
	 * @return array|false
	 */
	public function getItem($item_id, array $options = null)
	{
		if (!is_numeric($item_id)) {
			return false;
		}
		
		$sql = "SELECT * 
			FROM {$this->prefix}items
			WHERE item_id = '$item_id'
			AND entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			";
		$result = $this->DB->query($sql);
		if ($result->rowCount() == 1) {
			$row = $result->fetchObject();
			$item = array(
				'item_id'	=> $row->item_id,
				'uri_part'	=> $row->uri_part,
				'is_active'	=> $row->is_active,
				'owner_id'	=> $row->owner_id,
				'meta'		=> unserialize($row->meta),
				'create_datetime' => $row->create_datetime,
				);
		} else {
			return false;
		}
		
		// Формирование фрагмента SQL запроса для режимов отображения в списке и просмотр записи.
		$sql_conditions = '';
		if (isset($options['show_in_list']) and $options['show_in_list'] !== false) {
			$sql_conditions .= " AND p.show_in_list = '$options[show_in_list]' ";
		}
		
		if (isset($options['show_in_view']) and $options['show_in_view'] !== false) {
			$sql_conditions .= " AND p.show_in_view = '$options[show_in_view]' ";
		}
		
		// Получение списка свойств.
		$sql = "SELECT *
			FROM {$this->prefix}properties AS p,
				 {$this->prefix}properties_translation AS pt
			WHERE p.entity_id = '{$this->entity_id}'
			AND p.site_id = '{$this->Env->site_id}'
			AND pt.site_id = '{$this->Env->site_id}'
			AND pt.entity_id = p.entity_id
			AND p.property_id = pt.property_id 
			AND p.is_active = '1'
			$sql_conditions
			ORDER BY pos ASC
			";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			// Получение значений записи.
			$sql2 = "SELECT value
				FROM {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$row->name}
				WHERE item_id = '$item_id'
				";
			$result2 = $this->DB->query($sql2);
			if ($result2->rowCount() == 1) {
				$row2 = $result2->fetchObject();
				
				$original_value = false;
				if ($row->type == 'img' or $row->type == 'image' or $row->type == 'file') {
					$value = $this->Media->getFileUri($row2->value, unserialize($row->params));
					$original_value = $row2->value;
				} else {
					$value = $row2->value;
				}
				
				$item['content'][$row->name] = array(
					'property_id'	=> $row->property_id,
					'type'			=> $row->type,
					'title'			=> $row->title,
					'original_value'=> $original_value,
					'value'			=> $value,
					'params'		=> $row->params,
					'is_required'	=> $row->is_required,
					'show_in_view'	=> $row->show_in_view,
					'show_in_list'	=> $row->show_in_list,
					);
			}
		}
		
		// Категории.
		if (isset($options['categories']) and $options['categories'] == 1) {
			$sql = "SELECT icr.category_id, ct.title
				FROM {$this->prefix}items_categories_relation AS icr,
					 {$this->prefix}categories_translation AS ct
				WHERE icr.item_id = '$item_id'
				AND icr.entity_id = '{$this->entity_id}'
				AND ct.entity_id = '{$this->entity_id}'
				AND icr.site_id = '{$this->Env->site_id}'
				AND ct.site_id = '{$this->Env->site_id}'
				AND ct.category_id = icr.category_id
				";
			$result = $this->DB->query($sql);
			while ($row = $result->fetchObject()) {
				$item['categories'][$row->category_id] = $row->title;
			}
		}
		
		$item['tags'] = $this->getItemTagsList($item_id);
		
		return $item;
	}
	
	/**
	 * Удалить свойство записи по имени свойства.
	 * 
	 * @param int $item_id
	 * @param string $property_name
	 * @return void
	 * 
	 * @todo сделать проверку на успешность удаления.
	 */
	public function deleteItemProperty($item_id, $property_name)
	{
		$sql = "DELETE FROM {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property_name} WHERE item_id = '$item_id' ";
		$this->DB->exec($sql);
	}
	
	/**
	 * Получить значение свойства записи, по имеени свойства.
	 * 
	 * @param int $item_id - id звписи.
	 * @param string $property_name - Имя свойства.
	 * @return false|string - Значение свойства.
	 */
	public function getItemValue($item_id, $property_name)
	{
		$sql = "SELECT value
			FROM {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property_name}
			WHERE item_id = $item_id
			";
		$result = $this->DB->query($sql);
		if ($result->rowCount() == 1) {
			$row = $result->fetchObject();
			return $row->value;
		} else {
			return false;
		}
	}
	
	/**
	 * Обновление записи.
	 * 
	 * @param array $data
	 * @return bool - успешность выполнения операции.
	 * 
	 * @todo сделать транзакцию и блокировку.
	 */
	public function updateItem($data)
	{
		if (isset($data['category_id']) and is_numeric($data['category_id'])) {
			$category_id = $data['category_id'];
		} else {
			$category_id = 1;
		}

		if (!is_numeric($data['item_id'])) {
			return false;
		}
		
		// Обработка мета-тэгов.
		$meta = array();
		if (!empty($data['meta']['keywords'])) {
			$meta['keywords'] = $data['meta']['keywords'];
		}
		if (!empty($data['meta']['description'])) {
			$meta['description'] = $data['meta']['description'];
		}
		if (empty($meta)) {
			$meta = 'NULL';
		} else {
			$meta = $this->DB->quote(serialize($meta));
		}
		
		// Обработка части УРИ.
		// is_numeric(trim($data['uri_part']))
		if (strlen(trim($data['uri_part'])) == 0 or is_numeric(trim($data['uri_part']))) { // если ури парт не задан, то устанавливается в формате: item_id-Y-m-d
			//$uri_part = $data['item_id'] . '-' . date('Y-m-d');
			$uri_part = trim($data['item_id']);
		} else {
			$Helper_Uri = new Helper_Uri();
			$uri_part = $Helper_Uri->preparePart($data['uri_part']);
			$sql = "SELECT count(item_id) AS cnt
				FROM {$this->prefix}items
				WHERE site_id = '{$this->Env->site_id}'
				AND entity_id = '{$this->entity_id}'
				AND uri_part = {$this->DB->quote($uri_part)}
				AND item_id != $data[item_id]
				";
			$result = $this->DB->query($sql);
			$row = $result->fetchObject();
			if ($row->cnt > 0) {
				//$uri_part = $data['item_id'] . '-' . date('Y-m-d');
				$uri_part = trim($data['item_id']);
			}
		}
		
		if (isset($data['_delete_'])) {
			foreach ($data['_delete_'] as $key => $value) {
				$property_id = $this->getPropertyId($key);
				if ($value == 1 and $property_id !== false) {
					$image_id = $this->getItemValue($data['item_id'], $key);
					if (is_numeric($image_id)) {
						$sql = "SELECT params
							FROM {$this->prefix}properties
							WHERE entity_id = '{$this->entity_id}'
							AND property_id = '$property_id'
							AND site_id = '{$this->Env->site_id}'
							";
						$result = $this->DB->query($sql);
						$row = $result->fetchObject();
						
						if ($this->Media->deleteFile($image_id, unserialize($row->params))) {
							$this->deleteItemProperty($data['item_id'], $key);
						}
					}
				}
			}
		}

		// Обработка файлов.
		foreach ($_FILES as $key => $value) {
			if ($value['error'] == 0) {
				$sql = "SELECT params
					FROM {$this->prefix}properties
					WHERE entity_id = '{$this->entity_id}'
					AND site_id = '{$this->Env->site_id}'
					AND name = '$key' 
					";
				$result = $this->DB->query($sql);
				$row = $result->fetchObject();
				
				$data['content'][$this->getPropertyId($key)] = $this->Media->createFile($_FILES[$key], unserialize($row->params));
			}
		}
		
		$sql = "
			UPDATE {$this->prefix}items SET
				is_active = '$data[is_active]',
				uri_part = {$this->DB->quote($uri_part)},
				meta = $meta
			WHERE item_id = '$data[item_id]'
			AND entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			";
		$this->DB->exec($sql);

		$sql = "
			UPDATE {$this->prefix}items_categories_relation SET
				category_id = '$category_id'
			WHERE item_id = '$data[item_id]'
			AND entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			";
		$this->DB->exec($sql);
		
		// Обновление тегов.
		if (isset($data['tags'])) {
			foreach ($data['tags'] as $key => $value) {
				if ($value == 1) {
					$sql = "SELECT * 
						FROM {$this->prefix}tags_items_relation
						WHERE site_id = '{$this->Env->site_id}'
						AND entity_id = '{$this->entity_id}'
						AND item_id = '$data[item_id]'
						AND tag_id = '$key'
						";
					$result = $this->DB->query($sql);
					if ($result->rowCount() == 0) {
						$sql = "
							INSERT INTO {$this->prefix}tags_items_relation
								(item_id, tag_id, entity_id, site_id )
							VALUES
								('$data[item_id]', '$key', '{$this->entity_id}', '{$this->Env->site_id}' )
							";
						$this->DB->query($sql);
					}
				} else {
					$sql = "DELETE FROM {$this->prefix}tags_items_relation
						WHERE item_id = '$data[item_id]'
						AND entity_id = '{$this->entity_id}'
						AND site_id = '{$this->Env->site_id}'
						AND tag_id = '$key'
						";
					$this->DB->exec($sql);
				}
			}
		}
		
		// Обновление свойств
		foreach ($data['content'] as $key => $value) {
			$sql = "SELECT name
				FROM {$this->prefix}properties
				WHERE entity_id = '{$this->entity_id}'
				AND property_id = '$key'
				AND site_id = '{$this->Env->site_id}'
				";
			$result = $this->DB->query($sql);
			$row = $result->fetchObject();
			$property_name = $row->name;
			
			// Удаление свойства, если его значение пустое.
			if (strlen(trim($value)) == 0) {
				$sql = "DELETE FROM {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property_name}
					WHERE item_id = '$data[item_id]'
					";
				$this->DB->exec($sql);
				continue;
			}
			
			$sql = "SELECT *
				FROM {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property_name}
				WHERE item_id = '$data[item_id]'
				";
			$result = $this->DB->query($sql);
			// Свойство записи есть, обновлем его.
			if ($result->rowCount() == 1) {
				$row = $result->fetchObject();
				if (md5($this->DB->quote(trim($value))) != md5($this->DB->quote(trim($row->value)))) {
					$sql = "
						UPDATE {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property_name} SET
							value = " . $this->DB->quote(trim($value)) . "
						WHERE item_id = '$data[item_id]'
						";
					$this->DB->exec($sql);
				} 
			// Свойства записи нет, добавляем его.
			} else {
				$sql = "
					INSERT INTO {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property_name}
						(item_id, value)
					VALUES
						('$data[item_id]', " . $this->DB->quote(trim($value)) . " )
					";
				$this->DB->query($sql);
			}
		}
		return true;
	}
	
	/**
	 * Обновление категории.
	 *
	 * @param array $data
	 * @return bool - успешность выполнения операции.
	 */
	public function updateCategory($data)
	{
		if (!is_numeric($data['category_id'])) {
			return false;
		}

		// Обработка мета-тэгов.
		$meta = array();
		if (!empty($data['meta']['keywords'])) {
			$meta['keywords'] = $data['meta']['keywords'];
		}
		if (!empty($data['meta']['description'])) {
			$meta['description'] = $data['meta']['description'];
		}
		if (empty($meta)) {
			$meta = 'NULL';
		} else {
			$meta = $this->DB->quote(serialize($meta));
		}

		if ((int)$data['category_id'] === 1) {
			// Корневая категория.
			$pid = 0;
			$is_active = 1;
		} else {
			$pid = $data['pid'];
			$is_active = $data['is_active'];
		}
		
		// uri_part не может быть произвольным числом. При обновлении категории, если юзер ввел в качестве uri_part число, то вставялется ид категории.
		if (strlen(trim($data['uri_part'])) == 0 or is_numeric(trim($data['uri_part']))) {
			$uri_part = $data['category_id'];
		} else {
			// Проверка на уникальность uri_part в пределах родительской категории.
			$Helper_Uri = new Helper_Uri();
			$uri_part = $Helper_Uri->preparePart($data['uri_part']);
			$sql = "SELECT count(category_id) AS cnt
				FROM {$this->prefix}categories
				WHERE entity_id = '{$this->entity_id}'
				AND site_id = '{$this->Env->site_id}'
				AND uri_part = {$this->DB->quote($uri_part)}
				AND pid = '$pid'
				AND category_id != {$this->DB->quote($data['category_id'])}
				";
			$result = $this->DB->query($sql);
			$row = $result->fetchObject();
			if ($row->cnt > 0) {
				$uri_part = $data['category_id'];
			}
		}
		
		$sql = "
			UPDATE {$this->prefix}categories SET
				is_active = '$is_active',
				is_inheritance = '$data[is_inheritance]',
				pid = '$pid',
				pos = '$data[pos]',
				uri_part = '$uri_part'
			WHERE category_id = '$data[category_id]'
			AND entity_id = '{$this->entity_id}'
			";
		$this->DB->exec($sql);
		
		if ($data['category_id'] > 1) {
			$sql = "
				UPDATE {$this->prefix}categories_translation SET
					title = {$this->DB->quote(trim($data['title']))},
					meta = $meta
				WHERE category_id = '$data[category_id]'
				AND entity_id = '{$this->entity_id}'
				AND language_id = '{$this->Env->language_id}'
				";
			$this->DB->exec($sql);
		}
		
		return true;
	}
	
	/**
	 * Обновление тэга.
	 *
	 * @param array $data
	 * @return bool - успешность выполнения операции.
	 */
	public function updateTag($data)
	{
		$tag_id = $data['tag_id'];
		$title = $this->DB->quote($data['title']);
		$name = $this->DB->quote($data['name']);
		
		$sql = "
			UPDATE {$this->prefix}tags SET
				name = $name,
				pos = '$data[pos]',
				tags_group_id = '$data[tags_group_id]'
			WHERE tag_id = '$tag_id'
			AND entity_id = '{$this->entity_id}'
			";
		$this->DB->exec($sql);
		
		$sql = "
			UPDATE {$this->prefix}tags_translation SET
				title = $title
			WHERE tag_id = '$tag_id'
			AND entity_id = '{$this->entity_id}'
			AND language_id = '{$this->Env->language_id}'
			";
		$this->DB->exec($sql);
		
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
		$params = $this->DB->quote($data['params']);
		
		$sql = "
			UPDATE {$this->prefix}properties SET
				params = $params,
				pos = '$data[pos]',
				properties_group_id = '$data[properties_group_id]',
				is_active = '$data[is_active]',
				is_required = '$data[is_required]',
				show_in_admin = '$data[show_in_admin]',
				show_in_list = '$data[show_in_list]',
				show_in_view = '$data[show_in_view]'
			WHERE property_id = '$property_id'
			AND entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			";
		$this->DB->exec($sql);
		
		$sql = "
			UPDATE {$this->prefix}properties_translation SET
				title = $title
			WHERE property_id = '$property_id'
			AND entity_id = '{$this->entity_id}'
			AND language_id = '{$this->Env->language_id}'
			AND site_id = '{$this->Env->site_id}'
			";
		$this->DB->exec($sql);
		return true;
	}
	
	/**
	 * Обновление группы тэгов.
	 *
	 * @param array $data
	 * @return bool - успешность выполнения операции.
	 */
	public function updateTagsGroup($data)
	{
		$title = $this->DB->quote($data['title']);
		$name  = $this->DB->quote($data['name']);

		$sql = "
			UPDATE {$this->prefix}tags_groups SET
				name = $name,
				pos = '$data[pos]'
			WHERE tags_group_id = '$data[tags_group_id]'
			AND entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			";
		$this->DB->exec($sql);
		
		$sql = "
			UPDATE {$this->prefix}tags_groups_translation SET
				title = $title
			WHERE tags_group_id = '$data[tags_group_id]'
			AND entity_id = '{$this->entity_id}'
			AND language_id = '{$this->Env->language_id}'
			AND site_id = '{$this->Env->site_id}'
			";
		$this->DB->exec($sql);
	
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
				pos = '$data[pos]'
			WHERE properties_group_id = '$data[properties_group_id]'
			AND entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			";
		$this->DB->exec($sql);
		
		$sql = "
			UPDATE {$this->prefix}properties_groups_translation SET
				title = $title
			WHERE properties_group_id = '$data[properties_group_id]'
			AND entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			AND language_id = '{$this->Env->language_id}'
			";
		$this->DB->exec($sql);
		
		// Привязка групп свойств к категориям.
		$sql = " DELETE FROM {$this->prefix}properties_groups_category_relation
			WHERE site_id = '{$this->Env->site_id}'
			AND entity_id = '{$this->entity_id}'
			AND properties_group_id = '$data[properties_group_id]'
			";
		$this->DB->exec($sql);
		
		$categories = explode(',', $data['categories_relation']);
		foreach ($categories as $value) {
			if (is_numeric($value)) {
				$sql = "
					INSERT INTO {$this->prefix}properties_groups_category_relation
						(site_id, entity_id, properties_group_id, category_id)
					VALUES
						('{$this->Env->site_id}', '{$this->entity_id}', '$data[properties_group_id]', '$value')
					";
				$this->DB->query($sql);
			}
		}
	
		return true;
	}
	
	/**
	 * Получить прототип записи: кол-во и свойства полей.
	 * 
	 * @param int $category_id
	 * @param bool $is_admin - для админа генерируются все свойства, а не только активные.
	 * @return array
	 * 
	 * @todo добавить проверки на задействованные группы свойств, чтобы заново не собирать...
	 */
	public function getItemPrototype($category_id = 1, $is_admin = false)
	{
		if ($is_admin === false) {
			$is_admin = " AND p.is_active = '1' ";
		} else {
			$is_admin = '';
		}
		
		$prototype = array();
		while ($category_id > 0) {
			$sql = "
				SELECT
					p.*,
					pt.title AS title,
					pgt.title AS group_title,
					pg.name AS group_name
				FROM {$this->prefix}properties AS p,
					 {$this->prefix}properties_groups AS pg,
					 {$this->prefix}properties_groups_category_relation AS pgcr,
					 {$this->prefix}properties_groups_translation AS pgt,
					 {$this->prefix}properties_translation AS pt
				WHERE p.entity_id = '{$this->entity_id}'
				AND pgcr.entity_id = p.entity_id
				AND pgt.entity_id = p.entity_id
				AND pg.entity_id = p.entity_id
				AND pt.entity_id = p.entity_id

				AND (
					pgcr.category_id = '$category_id'
				
				)
				
				AND p.site_id = '{$this->Env->site_id}'
				AND pg.site_id = p.site_id
				AND pgcr.site_id = p.site_id
				AND pgt.site_id = p.site_id
				AND pt.site_id = p.site_id

				$is_admin
				
				AND p.property_id = pt.property_id

				AND pg.properties_group_id = pgcr.properties_group_id
				AND pg.properties_group_id = pgt.properties_group_id
				AND p.properties_group_id = pgcr.properties_group_id
				ORDER BY p.pos
				";
			$result = $this->DB->query($sql);
			
			if ($result->rowCount() > 0) {
				$properties = array();
				while ($row = $result->fetchObject()) {
					$properties[$row->property_id] = array(
						'title'			=> $row->title,
						'type'			=> $row->type,
						'name'			=> $row->name,
						'params'		=> $row->params,
						'is_required'	=> $row->is_required,
						'show_in_view'	=> $row->show_in_view,
						'show_in_list'	=> $row->show_in_list,
						'pos'			=> $row->pos
						);
					$group_name  = $row->group_name;
					$group_title = $row->group_title;
				}

				$prototype[$group_name] = array(
					'title'		 => $group_title,
					'properties' => $properties,
					);
			}
			$sql = "SELECT pid
				FROM {$this->prefix}categories
				WHERE category_id = '$category_id'
				AND entity_id = '{$this->entity_id}'
				AND site_id = '{$this->Env->site_id}'
				";
			$result = $this->DB->query($sql);
			$row = $result->fetchObject();
			$category_id = $row->pid;
		}
		return $prototype;
	}
	
	/**
	 * Создать категорию.
	 *
	 * @uses Helper_Uri
	 * 
	 * @param array $data
	 * @return bool
	 */
	public function createCategory($data)
	{
		$title = $this->DB->quote(trim($data['title']));

		if (is_numeric($data['pid']) and $data['pid'] > 0) {
			$pid = $data['pid'];
		} else {
			$pid = 1;
		}

		// Проверка на сущесвующую родительскую категорию. Если нет, то по умолчанию указывается родительская категория = 1.
		$sql = "SELECT * 
			FROM {$this->prefix}categories
			WHERE entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			AND category_id = '$pid'
			";
		$result = $this->DB->query($sql);
		if ($result->rowCount() != 1) {
			$pid = 1;
		}
		
		// Сначала создаётся категория с техническим uri_part.
		$uri_part = md5(microtime() . $this->Env->user_id . $data['uri_part']);
		
		// Вставка категории в БД.
		$sql = "
			INSERT INTO {$this->prefix}categories
				(entity_id, is_active, uri_part, pos, pid, owner_id, site_id, create_datetime )
			VALUES
				('{$this->entity_id}', '$data[is_active]', '$uri_part', '$data[pos]', '$pid', '{$this->Env->user_id}', '{$this->Env->site_id}', NOW() )
			";
		$this->DB->query($sql);
		$category_id = (int) $this->DB->lastInsertId();
		
		// Далее технический uri_part заменяется на нормальный.
		// @todo ПОЧЕМУ ;) uri_part не может быть произвольным числом, при создании категории, если юзер ввел в качестве uri_part число, то вставялется произвольный хэш.
		if (strlen(trim($data['uri_part'])) == 0 or is_numeric(trim($data['uri_part']))) {
			$uri_part = $category_id;
		} else {
			$Helper_Uri = new Helper_Uri();
			$uri_part = $Helper_Uri->preparePart($data['uri_part']);
			$sql = "SELECT count(category_id) AS cnt
				FROM {$this->prefix}categories
				WHERE entity_id = '{$this->entity_id}'
				AND site_id = '{$this->Env->site_id}'
				AND uri_part = {$this->DB->quote($uri_part)}
				AND pid = '$pid'
				";
			$result = $this->DB->query($sql);
			$row = $result->fetchObject();
			if ($row->cnt > 0) {
				//$uri_part = "$item_id-" . date('Y-m-d');
				$uri_part = $category_id;
			}
			// $uri_part = $row->max_category_id . '-' . $Helper_Uri->preparePart($data['title']);
		}
		
		$sql = "
			UPDATE {$this->prefix}categories SET
				uri_part = {$this->DB->quote($uri_part)}
			WHERE category_id = '$category_id'
			AND entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
		";
		$this->DB->query($sql);
				
		$sql = "
			INSERT INTO {$this->prefix}categories_translation
				(entity_id, category_id, language_id, title, site_id )
			VALUES
				('{$this->entity_id}', '$category_id', '{$this->Env->language_id}', $title, '{$this->Env->site_id}' )
			";
		$this->DB->exec($sql);
		return true;
	}
	
	/**
	 * Добавление записи.
	 * 
	 * @uses User
	 * 
	 * @param array $data
	 * @param int $category_id
	 * @return bool
	 * 
	 * @todo сделать транзакцию и блокировку.
	 * @todo сейчас какая то корявость с параметром $category_id :(
	 * @todo проверку на уникальность ури парта
	 */
	public function createItem($data, $category_id = 1)
	{
		if (isset($data['category_id']) and is_numeric($data['category_id'])) {
			$category_id = $data['category_id'];
		} else {
			$category_id = 1;
		}

		// Обработка мета-тэгов.
		$meta = array();
		if (!empty($data['meta']['keywords'])) {
			$meta['keywords'] = $data['meta']['keywords'];
		}
		if (!empty($data['meta']['description'])) {
			$meta['description'] = $data['meta']['description'];
		}
		if (empty($meta)) {
			$meta = 'NULL';
		} else {
			$meta = $this->DB->quote(serialize($meta));
		}

		$Date = new Helper_Date();
		
		// Сначала создаётся запись с техническим uri_part.
		$uri_part = md5(microtime() . $this->Env->user_id . $data['uri_part']);
		$sql = "
			INSERT INTO {$this->prefix}items
				(entity_id, is_active, uri_part, create_datetime, owner_id, site_id, meta)
			VALUES
				('{$this->entity_id}', '$data[is_active]', '$uri_part', '" . $Date->getDatetime() . "', '{$this->Env->user_id}', '{$this->Env->site_id}', $meta )
			";
		$this->DB->query($sql);
		$item_id = (int) $this->DB->lastInsertId();
		
		// Далее технический uri_part заменяется на нормальный.
		// is_numeric(trim($data['uri_part']))
		if (strlen(trim($data['uri_part'])) == 0 or is_numeric(trim($data['uri_part']))) {
			//$uri_part = "$item_id-" . date('Y-m-d');
			$uri_part = $item_id;
		} else {
			$Helper_Uri = new Helper_Uri();
			$uri_part = $Helper_Uri->preparePart($data['uri_part']);
			$sql = "SELECT count(item_id) AS cnt
				FROM {$this->prefix}items
				WHERE site_id = '{$this->Env->site_id}'
				AND entity_id = '{$this->entity_id}'
				AND uri_part = {$this->DB->quote($uri_part)}
				";
			$result = $this->DB->query($sql);
			$row = $result->fetchObject();
			if ($row->cnt > 0) {
				//$uri_part = "$item_id-" . date('Y-m-d');
				$uri_part = $item_id;
			}
		}
		$sql = "
			UPDATE {$this->prefix}items SET
				uri_part = '$uri_part'
			WHERE item_id = '$item_id'
			AND entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
		";
		$this->DB->query($sql);
		
		// Привязка записи к категориям.
		// @todo сделать универсальную привязку, например получая массив категорий в которые надо привязать.
		$sql = "
			INSERT INTO {$this->prefix}items_categories_relation
				(item_id, category_id, entity_id, site_id)
			VALUES
				('$item_id', '$category_id', '{$this->entity_id}', '{$this->Env->site_id}' )
			";
		$this->DB->exec($sql);

		// Если были загружены файлы, то они добавляются в медиа хранилище.
		foreach ($_FILES as $key => $value) {
			if ($value['error'] == 0) {
				$sql = "SELECT params
					FROM {$this->prefix}properties
					WHERE entity_id = '{$this->entity_id}'
					AND name = '$key' 
					AND site_id = '{$this->Env->site_id}'
					";
				$result = $this->DB->query($sql);
				$row = $result->fetchObject();
				
				$data['content'][$this->getPropertyId($key)] = $this->Media->createFile($_FILES[$key], unserialize($row->params));
			}
		}
		
		// Заполнение контента записи.
		foreach ($data['content'] as $key => $value) {
			$sql = "SELECT name
				FROM {$this->prefix}properties
				WHERE entity_id = '{$this->entity_id}'
				AND property_id = '$key' 
				AND site_id = '{$this->Env->site_id}'
				";
			$result = $this->DB->query($sql);
			$row = $result->fetchObject();
			
			if (strlen($value) > 0) {
				$sql = "
					INSERT INTO {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$row->name}
						(item_id, value)
					VALUES
						('$item_id', " . $this->DB->quote(trim($value)) . " )
					";
				$this->DB->exec($sql);
			}
		}
		
		// Добавление тегов.
		if (isset($data['tags'])) {
			foreach ($data['tags'] as $key => $value) {
				if ($value == 1) {
					$sql = "
						INSERT INTO {$this->prefix}tags_items_relation
							(item_id, tag_id, entity_id, site_id )
						VALUES
							('$item_id', '$key', '{$this->entity_id}', '{$this->Env->site_id}' )
						";
					$this->DB->query($sql);
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Получить id свойства, указав его имя.
	 * 
	 * @param string $name
	 * @param int|false
	 * 
	 * @todo УБРАТЬ! т.е. использовать имя в качестве ид. http://smart-core.org/task/index.php?do=details&task_id=140
	 */
	public function getPropertyId($name)
	{
		$sql = "SELECT property_id FROM {$this->prefix}properties WHERE name = '$name' AND entity_id = '{$this->entity_id}' AND site_id = '{$this->Env->site_id}' ";
		$result = $this->DB->query($sql);
		if ($result->rowCount() == 1) {
			$row = $result->fetchObject();
			return $row->property_id;
		} else {
			return false;
		}
	}
	
	/**
	 * Получить список групп тэгов.
	 *
	 * @param int $tags_group_id
	 * @return array
	 */
	public function getTagsGroupsList($tags_group_id = false)
	{
		$list = array();

		if ($tags_group_id === false) {
			$tags_group_id = '';
		} else {
			$tags_group_id = " AND tg.tags_group_id = '$tags_group_id' ";
		}
		
		$sql = "SELECT *
			FROM {$this->prefix}tags_groups AS tg,
				 {$this->prefix}tags_groups_translation AS tgt
			WHERE tg.entity_id = '{$this->entity_id}'
			AND tgt.entity_id = tg.entity_id
			AND tg.site_id = '{$this->Env->site_id}'
			AND tgt.site_id = tg.site_id
			AND tgt.language_id = '{$this->Env->language_id}'
			AND tg.tags_group_id = tgt.tags_group_id
			$tags_group_id
			ORDER BY tg.pos
			";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$list[$row->tags_group_id] = array(
				'name'	=> $row->name,
				'title' => $row->title,
				'pos'	=> $row->pos,
				);
		}
		return $list;
	}
	
	/**
	 * Получить список групп свойств.
	 *
	 * @param int|false $properties_group_id - если указан, то извлекаются только данные заданной группы, иначе все группы.
	 * @return array
	 */
	public function getPropertiesGroupsList($properties_group_id = false)
	{
		$list = array();
		
		if ($properties_group_id === false) {
			$properties_group_id = '';
		} else {
			$properties_group_id = " AND pg.properties_group_id = '$properties_group_id' ";
		}
		
		$sql = "SELECT * 
			FROM {$this->prefix}properties_groups AS pg,
				 {$this->prefix}properties_groups_translation AS pgt
			WHERE pg.entity_id = '{$this->entity_id}'
			AND pgt.entity_id = '{$this->entity_id}'
			AND pg.site_id = '{$this->Env->site_id}'
			AND pgt.site_id = '{$this->Env->site_id}'
			AND pgt.language_id = '{$this->Env->language_id}'
			AND pg.properties_group_id = pgt.properties_group_id
			$properties_group_id
			ORDER BY pg.pos
			";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$list[$row->properties_group_id] = array(
				'name'	=> $row->name,
				'title'	=> $row->title,
				'pos'	=> $row->pos,
				);
		}
		return $list;
	}
	
	/**
	 * Получить id тэга, по его имени.
	 *
	 * @param string $name
	 * @return int
	 */
	public function getTagId($name)
	{
		$sql = "SELECT * 
			FROM {$this->prefix}tags
			WHERE site_id = '{$this->Env->site_id}'
			AND entity_id = '{$this->entity_id}'
			AND name = {$this->DB->quote($name)}
			";
		$result = $this->DB->query($sql);
		if ($result->rowCount() == 1) {
			$row = $result->fetchObject();
			return $row->tag_id;
		} else {
			return false;
		}
	}
	
	/**
	 * Получить информацию о теге.
	 *
	 * @param int $tag_id
	 * @param array $options
	 * @return array
	 * 
	 * @todo мультияз
	 */
	public function getTagData($tag_id, array $options = null)
	{
		$sql = "
			SELECT * 
			FROM {$this->prefix}tags AS t,
				{$this->prefix}tags_translation AS tt
			WHERE t.site_id = '{$this->Env->site_id}'
			AND t.entity_id = '{$this->entity_id}'
			AND t.tag_id = {$this->DB->quote($tag_id)}
			AND t.tag_id = tt.tag_id
			AND t.site_id = tt.site_id
			AND t.entity_id = tt.entity_id
			";
		$result = $this->DB->query($sql);
		if ($result->rowCount() == 1) {
			// Настройка флага is_active.
			if (isset($options['is_active']) and $options['is_active'] == 1) {
				$is_active = ' AND i.is_active = 1 ';
			} elseif (isset($options['is_active']) and $options['is_active'] === 'all') {
				$is_active = '';
			} elseif (isset($options['is_active']) and $options['is_active'] == 0) {
				$is_active = ' AND i.is_active = 0 ';
			} else {
				$is_active = ' AND i.is_active = 1 ';
			}
			
			$row = $result->fetchObject();
			$sql2 = "SELECT count(tag_id) AS tags_count
				FROM {$this->prefix}tags_items_relation AS tir,
					{$this->prefix}items AS i
				WHERE i.site_id = '{$this->Env->site_id}'
				AND i.entity_id = '{$this->entity_id}'
				$is_active
				AND tir.tag_id = {$this->DB->quote($tag_id)}
				AND i.site_id = tir.site_id
				AND i.entity_id = tir.entity_id
				AND i.item_id = tir.item_id
				";
			$result2 = $this->DB->query($sql2);
			$row2 = $result2->fetchObject();

			$data = array(
				'name' => $row->name,
				'title' => $row->title,
				'count' => $row2->tags_count,
				);
			return $data;
		} else {
			return false;
		}
	}
	
	/**
	 * Получить список тэгов в группе.
	 *
	 * @param int|false $tags_group_id
	 * @param bool $items_count - производить подсчет кол-ва записей для каждого свойства.
	 * @return array
	 */
	public function getTagsList($tags_group_id = false, $items_count = true)
	{
		$list = array();
		
		if ($tags_group_id === false) {
			$tags_group_id = '';
		} else {
			$tags_group_id = " AND t.tags_group_id = '$tags_group_id' ";
		}
		
		$sql = "SELECT * 
			FROM {$this->prefix}tags AS t,
				 {$this->prefix}tags_translation AS tt
			WHERE t.entity_id = '{$this->entity_id}'
			AND tt.entity_id = t.entity_id
			AND t.site_id = '{$this->Env->site_id}'
			AND tt.site_id = t.site_id
			AND tt.language_id = '{$this->Env->language_id}'
			AND t.tag_id = tt.tag_id
			$tags_group_id
			ORDER BY t.pos
			";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$cnt = 0;
			if ($items_count) {
				$sql2 = "SELECT count(item_id) AS cnt
					FROM {$this->prefix}tags_items_relation
					WHERE entity_id = '{$this->entity_id}'
					AND site_id = '{$this->Env->site_id}'
					AND tag_id = '{$row->tag_id}'
					";
				$result2 = $this->DB->query($sql2);
				$row2 = $result2->fetchObject();
				$cnt = $row2->cnt;	
			}
			
			$list[$row->tag_id] = array(
				'name' => $row->name,
				'title' => $row->title,
				'tags_group_id' => $row->tags_group_id,
				'pos' => $row->pos,
				'items_count' => $cnt,
				);
		}
		
		return $list;
	}
	
	/**
	 * Получить список свойств в группе.
	 * Если группа не указана, возвращается список всех свойств в экземпляре.
	 *
	 * @param int|false $properties_group_id
	 * @param bool $items_count - производить подсчет кол-ва записей для каждого свойства.
	 * @param bool $is_admin - для админа генерируются все свойства, а не только активные.
	 * @return array
	 */
	public function getPropertiesList($properties_group_id = false, $items_count = true, $is_admin = false)
	{
		$list = array();
		
		if ($properties_group_id === false) {
			$properties_group_id = '';
		} else {
			$properties_group_id = " AND p.properties_group_id = '$properties_group_id' ";
		}
		
		if ($is_admin === false) {
			$is_admin = " AND p.is_active = '1' ";
		} else {
			$is_admin = '';
		}
		
		$sql = "SELECT * 
			FROM {$this->prefix}properties AS p,
				 {$this->prefix}properties_translation AS pt
			WHERE p.entity_id = '{$this->entity_id}'
			AND pt.entity_id = '{$this->entity_id}'
			AND p.site_id = '{$this->Env->site_id}'
			AND pt.site_id = '{$this->Env->site_id}'
			AND pt.language_id = '{$this->Env->language_id}'
			AND p.property_id = pt.property_id
			$properties_group_id
			$is_admin
			ORDER BY p.pos
			";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$cnt = false;
			if ($items_count) {
				$sql2 = "SELECT count(item_id) AS cnt
					FROM {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$row->name}
					";
				$result2 = $this->DB->query($sql2);
				$row2 = $result2->fetchObject();
				$cnt = $row2->cnt;	
			}
			$list[$row->property_id] = array(
				'name' 			=> $row->name,
				'title' 		=> $row->title,
				'properties_group_id' => $row->properties_group_id,
				'is_active'		=> $row->is_active,
				'is_required'	=> $row->is_required,
				'show_in_admin'	=> $row->show_in_admin,
				'show_in_list'	=> $row->show_in_list,
				'show_in_view'	=> $row->show_in_view,
				'type'			=> $row->type,
				'params'		=> $row->params,
				'pos'			=> $row->pos,
				'items_count'	=> $cnt,
				);
		}
		return $list;
	}
	
	/**
	 * Удаление категории.
	 *
	 * @param int $category_id
	 * @param bool $hard - жесткое удаление категории.
	 * @return bool
	 */
	public function deleteCategory($category_id, $hard = true)
	{
		if (!is_numeric($category_id) or (int)$category_id === 1) {
			return false;
		}
		
		// Удалить можно только категорию, которая не включает в себя другие категории, а также не содержит записей.
		if (count($this->getCategoryInheritanceList($category_id)) == 0 and $this->getItemsCount(array('categories' => $category_id)) == 0) {
			// @todo можно запаковать в один запрос ;)
			$sql = " DELETE FROM {$this->prefix}categories
				WHERE entity_id = '{$this->entity_id}'
				AND site_id = '{$this->Env->site_id}'
				AND category_id = '$category_id'
				";
			$this->DB->exec($sql);
			$sql = " DELETE FROM {$this->prefix}categories_translation
				WHERE entity_id = '{$this->entity_id}'
				AND site_id = '{$this->Env->site_id}'
				AND category_id = '$category_id'
				";
			$this->DB->exec($sql);
			return true;
		} else {
			return false;
		}
		
	}
	
	/**
	 * Мягкое удаление записи.
	 * 
	 * @param int $item_id
	 */
	public function deleteItem($item_id)
	{
		$sql = "
			UPDATE {$this->prefix}items SET
				is_deleted = 1
			WHERE item_id = '$item_id'
			AND entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			";
		$this->DB->exec($sql);
	}
	
	/**
	 * Создать новый экземляр каталога.
	 * 
	 * Так же сразу создаётся корневая категория для новго экземпляра.
	 *
	 * @param string $name
	 * @param string $title
	 * @return $entity_id
	 * 
	 * @todo обернуть инсерты в тразакцию.
	 */
	public function createEntity($name, $title)
	{
		$name = $this->DB->quote($name);
		$title = $this->DB->quote($title);
		
		// Вычисляется максимальная позиция, чтобы новый экземпляр был помещен последним.
		$sql = "SELECT max(pos) AS max_pos FROM {$this->prefix}entities WHERE site_id = '{$this->Env->site_id}'";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		$pos = $row->max_pos + 1;

			
		// Вставка экземпляра.
		$sql = "
			INSERT INTO {$this->prefix}entities
				(name, language_id, pos, site_id, create_datetime, owner_id)
			VALUES
				($name, '{$this->Env->language_id}', '$pos', '{$this->Env->site_id}', NOW(), '{$this->Env->user_id}' )
			";
		$this->DB->query($sql);
		$entity_id = (int) $this->DB->lastInsertId();
		
		// Вставка перевода экземпляра.
		$sql = "
			INSERT INTO {$this->prefix}entities_translation
				(entity_id, language_id, title, site_id)
			VALUES
				('$entity_id', '{$this->Env->language_id}', $title, '{$this->Env->site_id}')
			";
		$this->DB->exec($sql);
		
		// Вставка корневой категории.
		$sql = "
			INSERT INTO {$this->prefix}categories
				(category_id, entity_id, uri_part, site_id, create_datetime, owner_id)
			VALUES
				('1', '$entity_id', '', '{$this->Env->site_id}', NOW(), '{$this->Env->user_id}')
			";
		$this->DB->exec($sql);
		
		return $entity_id;
	}
	
	/**
	 * Создание группы свойств.
	 *
	 * @param array $data
	 * @return bool - успешность выполнения операции.
	 */
	public function createPropertiesGroup($data)
	{
		$title = $this->DB->quote($data['title']);
		$name  = $this->DB->quote($data['name']);
		
		// Вычисляется максимальная позиция, чтобы новое свойство было помещено последним.
		$sql = "SELECT max(pos) AS max_pos 
			FROM {$this->prefix}properties_groups
			WHERE entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		$pos = $row->max_pos + 1;

		$sql = "
			INSERT INTO {$this->prefix}properties_groups
				(entity_id, name, pos, site_id)
			VALUES
				('{$this->entity_id}', $name, '$pos', '{$this->Env->site_id}' )
			";
		$this->DB->query($sql);
		$properties_group_id = (int) $this->DB->lastInsertId();
		
		$sql = "
			INSERT INTO {$this->prefix}properties_groups_translation
				(properties_group_id, entity_id, language_id, title, site_id )
			VALUES
				('$properties_group_id', '{$this->entity_id}', '{$this->Env->language_id}', $title, '{$this->Env->site_id}' )
			";
		$this->DB->exec($sql);
		
		// По умолчанию группа привязывается к корневой категории. (id = 1)
		$sql = "
			INSERT INTO {$this->prefix}properties_groups_category_relation
				(properties_group_id, category_id, entity_id, site_id)
			VALUES
				('$properties_group_id', '1', '{$this->entity_id}', '{$this->Env->site_id}' )
			";
		$this->DB->query($sql);
		
		return true;
	}
	
	/**
	 * Создание группы тэгов.
	 *
	 * @param array $data
	 * @return bool - успешность выполнения операции.
	 */
	public function createTagsGroup($data)
	{
		$title = $this->DB->quote($data['title']);
		$name = $this->DB->quote($data['name']);

		// Вычисляется максимальная позиция, чтобы новое свойство было помещено последним.
		$sql = "SELECT max(pos) AS max_pos 
			FROM {$this->prefix}tags_groups
			WHERE entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		$pos = $row->max_pos + 1;

		$sql = "
			INSERT INTO {$this->prefix}tags_groups
				(entity_id, name, pos, site_id)
			VALUES
				('{$this->entity_id}', $name, '$pos', '{$this->Env->site_id}')
			";
		$this->DB->query($sql);
		$tags_group_id = (int) $this->DB->lastInsertId();
		
		$sql = "
			INSERT INTO {$this->prefix}tags_groups_translation
				(tags_group_id, entity_id, language_id, title, site_id )
			VALUES
				('$tags_group_id', '{$this->entity_id}', '{$this->Env->language_id}', $title, '{$this->Env->site_id}' )
			";
		$this->DB->exec($sql);
		
		return true;
	}
	
	/**
	 * Создание тэга.
	 *
	 * @param array $data
	 * @return bool - успешность выполнения операции.
	 */
	public function createTag($data)
	{
		$title = $this->DB->quote($data['title']);
		$name = $this->DB->quote($data['name']);

		// Вычисляется максимальная позиция, чтобы новое свойство было помещено последним.
		$sql = "SELECT max(pos) AS max_pos 
			FROM {$this->prefix}tags
			WHERE entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		$pos = $row->max_pos + 1;
		
		$sql = "
			INSERT INTO {$this->prefix}tags
				(entity_id, name, pos, tags_group_id, site_id, create_datetime, owner_id )
			VALUES
				('{$this->entity_id}', $name, '$pos', '$data[tags_group_id]', '{$this->Env->site_id}', NOW(), '{$this->Env->user_id}' )
			";
		$this->DB->query($sql);
		$tag_id = (int) $this->DB->lastInsertId();
		
		$sql = "
			INSERT INTO {$this->prefix}tags_translation
				(tag_id, entity_id, language_id, title, site_id )
			VALUES
				('$tag_id', '{$this->entity_id}', '{$this->Env->language_id}', $title, '{$this->Env->site_id}' )
			";
		$this->DB->exec($sql);

		return true;
	}
	
	/**
	 * Создание свойства.
	 *
	 * @param array $data
	 * @return bool - успешность выполнения операции.
	 */
	public function createProperty($data)
	{
		$title = $this->DB->quote($data['title']);
		$name  = $this->DB->quote($data['name']);
		$type  = $this->DB->quote($data['type']);

		// Вычисляется максимальная позиция, чтобы новое свойство было помещено последним.
		$sql = "SELECT max(pos) AS max_pos 
			FROM {$this->prefix}properties
			WHERE entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		$pos = $row->max_pos + 1;
		
		$sql = "
			INSERT INTO {$this->prefix}properties
				(entity_id, name, type, pos, properties_group_id, is_required, show_in_admin, show_in_list, show_in_view, site_id, create_datetime, owner_id )
			VALUES
				('{$this->entity_id}', $name, $type, '$pos', '$data[properties_group_id]', '$data[is_required]', '$data[show_in_admin]', '$data[show_in_list]', '$data[show_in_view]', '{$this->Env->site_id}', NOW(), '{$this->Env->user_id}' )
			";
		$this->DB->query($sql);
		$property_id = (int) $this->DB->lastInsertId();
		
		$sql = "
			INSERT INTO {$this->prefix}properties_translation
				(property_id, entity_id, language_id, title, site_id )
			VALUES
				('$property_id', '{$this->entity_id}', '{$this->Env->language_id}', $title, '{$this->Env->site_id}' )
			";
		$this->DB->exec($sql);

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
		$sql ="
			CREATE TABLE IF NOT EXISTS {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$data['name']} (
			  item_id bigint(20) unsigned NOT NULL,
			  value $value_type,
			  PRIMARY KEY (item_id),
			  KEY value (value$key)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Тип: {$data['type']}'
			";
		$this->DB->exec($sql);
		return true;
	}
	
	/**
	 * Получить ссылку на редактирование записи.
	 *
	 * @param int $id - ID записи.
	 * @return
	 */
	public function getEditItemLink($id, $path = '')
	{
		return $this->action_path . $path . '?edit_item=' . $id;
	}

	/**
	 * Получить список категорий в которые подключена группа свойств.
	 *
	 * @param int $properties_group_id
	 * @return array
	 */
	public function getPropertyGroupCategoryRelationList($properties_group_id)
	{
		$categories = array();
		$sql = "SELECT * 
			FROM {$this->prefix}properties_groups_category_relation
			WHERE site_id = '{$this->Env->site_id}'
			AND entity_id = '{$this->entity_id}'
			AND properties_group_id = '$properties_group_id'
			";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$categories[$row->category_id] = $this->getCategoryData($row->category_id);
		}
		return $categories;
	}
	
	 /**
	  * Обработчик POST данных.
	  *
	  * @param array $pd
	  * @param string $submit
	  * @return
	  */
	 public function postProcessor($pd, $submit)
	 {
		switch ($submit) {
			case 'create_category':
				$this->createCategory($pd);
				cf_redirect('?categories');
				break;
			case 'delete_category':
				$this->deleteCategory($pd['category_id']);
				cf_redirect('?categories');
				break;
			case 'upadate_category':
				$this->updateCategory($pd);
				cf_redirect('?categories');
				break;
			case 'create_entity':
				$this->createEntity($pd['name'], $pd['title']);
				break;
			case 'create_property':
				$this->createProperty($pd);
				cf_redirect('?edit_properties_group=' . $pd['properties_group_id']);
				break;
			case 'update_property':
				$this->updateProperty($pd);
				cf_redirect('?edit_properties_group=' . $pd['properties_group_id']);
				break;
			case 'create_properties_group':
				$this->createPropertiesGroup($pd);
				cf_redirect('?properties');
				break;
			case 'update_properties_group':
				$this->updatePropertiesGroup($pd);
				cf_redirect('?edit_properties_group=' . $pd['properties_group_id']);
				break;
			case 'create_tag':
				$this->createTag($pd);
				cf_redirect('?edit_tags_group=' . $pd['tags_group_id']);
				break;
			case 'create_tags_group':
				$this->createTagsGroup($pd);
				cf_redirect('?tags');
				break;
			case 'update_tags_group':
				$this->updateTagsGroup($pd);
				cf_redirect('?edit_tags_group=' . $pd['tags_group_id']);
				break;
			case 'update_tag':
				$this->updateTag($pd);
				cf_redirect('?edit_tags_group=' . $pd['tags_group_id']);
				break;
			case 'create_item':
				$this->createItem($pd);
				cf_redirect($this->getUriByCategoryId($pd['category_id']));
				break;
			case 'update_item':
				$this->updateItem($pd);
				if (isset($_POST['return_to'])) {
					cf_redirect($_POST['return_to']);
				} else {
					cf_redirect($this->getUriByCategoryId($pd['category_id']));
				}
				break;
			case 'delete_item':
				if (is_numeric($pd['item_id'])) {
					$this->deleteItem($pd['item_id']);
				}
				cf_redirect($this->getUriByCategoryId($pd['category_id']));
				break;
			case 'delete_property':
				if (is_numeric($pd['property_id'])) {
					$this->deleteProperty($pd['property_id']);
				}
				cf_redirect('?edit_properties_group=' . $pd['properties_group_id']);
				break;
			default;
		}
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
			AND property_id = '$property_id'
			";
		$this->DB->exec($sql);
		
		$sql = "DELETE FROM {$this->prefix}properties_translation
			WHERE site_id = '{$this->Env->site_id}'
			AND entity_id = '{$this->entity_id}'
			AND property_id = '$property_id'
			";
		$this->DB->exec($sql);
		
		$sql = "DROP TABLE {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property['name']}";
		$this->DB->exec($sql);
		
		return true;
	}
	 
	/**
	 * Установить число записей на страницу.
	 *
	 * @param int $num
	 * @return void
	 */
	public function setItemsPerPage($num)
	{
		$this->items_per_page = $num;
	}
	
	/**
	 * Установить текущую страницу.
	 *
	 * @param int $num
	 * @return void
	 */
	public function setPageNum($num)
	{
		$this->current_page = $num;		
	}
	
	/**
	 * Получить список экземпляров.
	 *
	 * @return array
	 */
	public function getEntitiesList()
	{
		$list = array();
		$sql = "SELECT * 
			FROM {$this->prefix}entities
			WHERE site_id = '{$this->Env->site_id}'
			";
		$result = $this->DB->query($sql);
		if (!empty($result) and $result->rowCount() > 0) {
			while ($row = $result->fetchObject()) {
				$list[$row->entity_id] = $row->name;
			}
		}
		return $list;
	}
	
}
