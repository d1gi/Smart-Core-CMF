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
 * @version 2011-12-30.0
 */
class Component_Unicat extends Base
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
	 * Вспомогательная перемення, для быстрого формирования ссылки на действия.
	 * @var string
	 */
	protected $action_path;
	
	/**
	 * Нода.
	 * @var object
	 */
	protected $Node;
	
	/**
	 * Массив со структурами, где ключи являются просто порядковыми номерами.
	 * @var array
	 */
	protected $structures;
	
	/**
	 * Флаг наличия таблиц в БД.
	 * @var bool
	 */
	protected $is_tables_exist;
	
	/**
	 * Обязательные свойства записей.
	 * @var array|false
	 */
	protected $requred_items_properties;
		
	/**
	 * Обязательные стуктуры.
	 * @var array|false
	 */
	protected $requred_structures;
	
	protected $path_prefix;
		
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
		
		$this->prefix				= isset($params['unicat_db_prefix']) ? $params['unicat_db_prefix'] : '';
		$this->requred_items_properties = isset($params['requred_items_properties']) ? $params['requred_items_properties'] : false;
		$this->requred_structures	= isset($params['requred_structures']) ? $params['requred_structures'] : false;
		$this->path_prefix			= isset($params['path_prefix']) ? $params['path_prefix'] : '';
		$this->Node					= $params['node'];
		$this->action_path			= $this->Env->current_folder_path . ACTION . '/' . $this->Node->id . '/';
		$this->entity_id			= $params['entity_id'];
		$this->current_page			= 1;
		$this->items_per_page		= 10;
		$this->setTpl(false);
		
		// Проверка на наличие таблиц (пока смотрим только entities).
		$result = $this->DB->query("SHOW TABLES LIKE '{$this->prefix}entities'");
		if ($result->rowCount() == 0) {
			$this->is_tables_exist = false;
			return false;
		} else {
			$this->is_tables_exist = true;
		}
		
		$this->structures			= $this->getStructuresList();
		$this->Media				= new Component_Media($params['media_collection_id']);
	}
	
	/**
	 * Получить флаг существуют ли таблицы.
	 *
	 * @return bool
	 */
	public function isTablesExist()
	{
		return $this->is_tables_exist;
	}
	
	/**
	 * Получить свойства категории.
	 *
	 * @param
	 * @return
	 * 
	 * @todo ПЕРЕДЕЛАТЬ :)
	 */
	public function getCategoryProperties($category_id)
	{
		$properties = array();
		$sql = "SELECT name, value
			FROM {$this->prefix}categories_properties
			WHERE entity_id = {$this->entity_id}
			AND category_id = '$category_id'
			AND site_id = '{$this->Env->site_id}'
			ORDER BY pos ";
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
		if (empty($items)) {
			return null;
		}
		
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
	 * @param int $structure_id
	 * @param int $parent_id
	 * @param int|false $max_depth - максимальная вложенность в уровнях, false - не учитывать глубину.
	 * @param 1|0|'all' $is_active - учитывать активность.
	 * 
	 * @return array
	 */
	public function getCategoriesList($structure_id, $parent_id, $max_depth = false, $is_active = 1)
	{
		$this->_category_tree_list_arr = array();
		$this->_buildCategoriesTree($this->getCategoriesTree($structure_id, $parent_id, $max_depth, $is_active));
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
	 * @param int $structure_id
	 * @param int $parent_id
	 * @param int $max_depth
	 * @return array
	 * 
	 * @todo мультиязычность! пока что выполняется одноязычный запрос.
	 */
	public function getCategoriesTree($structure_id, $parent_id, $max_depth = false, $is_active = 1)
	{
		$sql_categories_table = $this->getStructureData('id', $structure_id, 'table');
		
		if ($sql_categories_table === null) {
			return null;
		}
		
		$uri = parse_url($_SERVER['REQUEST_URI']);
		$current_path = $uri['path'];
		
		// Настройка флага is_active.
		if ($is_active === 0 or $is_active === false) {
			$sql_is_active = ' AND is_active = 0 ';
		} elseif ($is_active === 'all') {
			$sql_is_active = '';
		} else {
			$sql_is_active = ' AND is_active = 1 ';
		}
		
		$this->_category_tree_level++;
		$items = array();

		$sql = "SELECT *
			FROM $sql_categories_table
			WHERE pid = '$parent_id'
			$sql_is_active
			ORDER BY pos ";
		$result = $this->DB->query($sql);
		while($row = $result->fetchObject()) {
			// копаем до указанной глубины.
			if ($max_depth != false and $max_depth < $this->_category_tree_level) {
				continue;
			}

			$uri = $this->getUriByCategoryId($structure_id, $row->category_id);
			$selected = strpos($current_path, $uri) === 0 ? 1 : 0;
			
			$items[$row->category_id] = array(
				'selected' => $selected,
				'uri' => $uri,
				'title' => $row->title,
				'uri_part' => $row->uri_part,
				'is_active' => $row->is_active,
				'pid' => $row->pid,
				'pos' => $row->pos,
				'items' => $this->getCategoriesTree($structure_id, $row->category_id, $max_depth, $is_active),
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
	public function getCategoryData($structure_id, $category_id)
	{
		$sql_cat_table = $this->getStructureData('id', $structure_id, 'table');
		$cat = $this->DB->getRow("SELECT * FROM $sql_cat_table WHERE category_id = '$category_id' ");
		if (empty($cat)) {
			return null;
		} else {
			$cat['meta'] = unserialize($cat['meta']);
			return $cat;
		}
	}
	
	/**
	 * Получение списка наследуемых категорий.
	 *
	 * @param
	 * @return array 
	 */
	public function getCategoryInheritanceList($structure_id, $category_id)
	{
		$this->_category_inheritance_list = array();
		$this->_buildCategoryInheritanceList($structure_id, $category_id);
		return $this->_category_inheritance_list;
	}
	
	/**
	 * Вспомогательный метод рекурсивного формирования списка наследованных категорий.
	 *
	 * @param int $category_id
	 * @return void
	 */
	protected function _buildCategoryInheritanceList($structure_id, $category_id)
	{
		$sql_cat_table = $this->getStructureData('id', $structure_id, 'table');
		
		$sql = "SELECT is_inheritance
			FROM $sql_cat_table
			WHERE category_id = '$category_id'
			AND is_active = 1 ";
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
			FROM $sql_cat_table
			WHERE pid = '$category_id'
			AND is_inheritance = 1
			AND is_active = 1 ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$this->_category_inheritance_list[] = $row->category_id;
			$this->_buildCategoryInheritanceList($structure_id, $row->category_id);
		}
	}
	
	/**
	 * Обновить связи записей со структурами.
	 *
	 * @param int $item_id
	 * @param array $data
	 * @return bool
	 */
	protected function _updateItemsStructuresRelation($item_id, $data)
	{
		// Привязка записи к структурам категорий.
		$structs_tmp = array();
		foreach ($data as $structure_id => $categories) {
			// $categories - может быть числом, тогда считается, что это единственная категория, либо массив, тогда множесвенное вхождение.
			// Пропускаем экземпляр
			if ($structure_id == 0 and $categories == 1) {
				$structs_tmp[0][0] = '';
				continue;
			}
			
			if (is_numeric($categories) and $categories == 0) {
				continue;
			}
			
			$sql_categories_table = $this->getStructureData('id', $structure_id, 'table');
			
			// Собирается массив $structs_tmp.
			// Множественные вхождения.
			if (is_array($categories)) {
				foreach ($categories as $category_id => $is_enable) {
					if ($is_enable == 1) {
						
						$structs_tmp[$structure_id][$category_id] = true;
						$pid = $category_id;
						while ($pid != 0) {
							// Сначала достаётся pid.
							$sql = "SELECT pid FROM $sql_categories_table WHERE category_id = '$pid'";
							$result = $this->DB->query($sql);
							$row = $result->fetchObject();
							$pid = $row->pid;
							
							// Вычисление наследования.
							$sql = "SELECT category_id, is_inheritance FROM $sql_categories_table WHERE category_id = '$pid'";
							$result = $this->DB->query($sql);
							if ($result->rowCount() == 1) {
								$row = $result->fetchObject();
								
								if ($row->is_inheritance == 0) {
									break;
								} else {
									$structs_tmp[$structure_id][$row->category_id] = true;
								}
							} else {
								break;
							}
						}
					}					
				}
			} 
			// Одиночное вхождение.
			else if (is_numeric($categories)) {				
				$category_id = $categories;
				
				$structs_tmp[$structure_id][$category_id] = true;
				$pid = $category_id;
				while ($pid != 0) {
					// Сначала достаётся pid.
					$sql = "SELECT pid FROM $sql_categories_table WHERE category_id = '$pid'";
					$result = $this->DB->query($sql);
					$row = $result->fetchObject();
					$pid = $row->pid;
					
					// Вычисление наследования.
					$sql = "SELECT category_id, is_inheritance FROM $sql_categories_table WHERE category_id = '$pid'";
					$result = $this->DB->query($sql);
					if ($result->rowCount() == 1) {
						$row = $result->fetchObject();
						
						if ($row->is_inheritance == 0) {
							break;
						} else {
							$structs_tmp[$structure_id][$row->category_id] = true;
						}
					} else {
						break;
					}
				}
			}
		} // __end foreach ($data['structure'] ... )

		// Удаление всех привязок записи к структурам.
		$sql = "DELETE FROM {$this->prefix}items_structures_relation
			WHERE entity_id = '{$this->entity_id}'
			AND item_id = '$item_id' ";
		$this->DB->exec($sql);
		
		$sql = "DELETE FROM {$this->prefix}items_structures_relation_single
			WHERE entity_id = '{$this->entity_id}'
			AND item_id = '$item_id' ";
		$this->DB->exec($sql);

		// Если ни одна категория не задана, то  привязывается к экземпляру.
		if (count($structs_tmp) == 0) {
			$structs_tmp[0][0] = '';
		}

		foreach ($structs_tmp as $structure_id => $categoryes) {
			foreach ($categoryes as $category_id => $__dummy) {
				$sql = "
					INSERT INTO {$this->prefix}items_structures_relation
						(structure_id, category_id, item_id, entity_id)
					VALUES
						('$structure_id', '$category_id', '$item_id', '{$this->entity_id}' ) ";
				$this->DB->exec($sql);		
			}
		}
		
		// Запомнинание установленных вхождений (без учета наследования).
		foreach ($this->structures as $structure) {
			if (isset($data[$structure['id']])) {
				// single режим
				if (is_numeric($data[$structure['id']])) {
					$category_id = $data[$structure['id']];					
				} else if (is_array($data[$structure['id']])) {
					foreach ($data[$structure['id']] as $category_id => $is_enable) {
						if ($is_enable == 1) {
							$sql = "
								INSERT INTO {$this->prefix}items_structures_relation_single
									(structure_id, category_id, item_id, entity_id)
								VALUES
									('{$structure['id']}', '$category_id', '$item_id', '{$this->entity_id}' ) ";
							$this->DB->exec($sql);		
						}
					}
					continue;
				}
			} else {
				$category_id = 0;
			}
			
			$sql = "
				INSERT INTO {$this->prefix}items_structures_relation_single
					(structure_id, category_id, item_id, entity_id)
				VALUES
					('{$structure['id']}', '$category_id', '$item_id', '{$this->entity_id}' ) ";
			$this->DB->exec($sql);		
		}
		
		return true;
	}
	
	/**
	 * Получить ссылку на запись по её ID.
	 *
	 * @param int $item_id
	 * @return string|false
	 * 
	 * @todo СДЕЛАТЬ! :) Получить ссылку на запись по её ID.
	 */
	public function getUriByItemId($item_id)
	{
	
		return false;
	}
	
	/**
	 * Получить полную ссылку на категорию. Вместе с путём к папке, куда подключен модуль.
	 *
	 * @param int $category_id
	 * @return string
	 * 
	 * @todo multilang
	 */
	public function getUriByCategoryId($structure_id, $category_id)
	{
		$uri_parts = array();
		$uri = '';
		
		$table = $this->getStructureData('id', $structure_id, 'table');
		
		while($category_id != 0) {
			$sql = "SELECT pid, uri_part
				FROM $table
				WHERE category_id = '$category_id'
				LIMIT 1 ";
			$row = $this->DB->getRow($sql);
			
			$category_id = $row['pid'];
			$uri_parts[] = $row['uri_part'];
		}
		
		$uri_parts = array_reverse($uri_parts);
		foreach ($uri_parts as $value) {
			$uri .= $value . '/';
		}
	
		return Folder::getUri($this->Node->folder_id) . $uri;
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
		$items			 = array();
		$sql_comparisons = '';
		$sql_joins		 = array();
		$sql_orders		 = array();
		
		// Устанавливается $link_postfix, по умолчанию ".html".
		$link_postfix = isset($options['link']['postfix']) ? $options['link']['postfix'] : '.html';
		
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
		$sql_structures = '';
		if (!isset($options['structure']) or empty($options['structure'])) {
			$sql_structures = '';
		} else {
			$sql_structures = " AND ( ";
			$cnt = 0;
			foreach ($options['structure'] as $structure_id => $category_id) {
				if ($cnt++ > 0) {
					$sql_structures .= " \nOR ";
				}
				$sql_structures .= " (isr.structure_id = $structure_id AND isr.category_id = $category_id) ";
			}
			$sql_structures .= ' ) ';
			unset($cnt);
		}
		
		// Постраничность.
		// Устанавливается $current_page, по умолчанию $this->current_page.
		$current_page = (isset($options['paginator']['current_page']) and is_numeric($options['paginator']['current_page'])) ? $options['paginator']['current_page'] : $this->current_page;
		
		if (isset($options['paginator']['items_per_page']) and is_numeric($options['paginator']['items_per_page'])) {
			$this->items_per_page = $options['paginator']['items_per_page'];
		}
		
		if ($this->items_per_page == 0) {
			$sql_limit = '';
		} else {
			$start_item = ($current_page - 1) * $this->items_per_page;
			$sql_limit = " LIMIT $start_item, {$this->items_per_page} ";
		}
		
		// Фильтры.
		$filters = array();
		if (isset($options['filters']) and is_array($options['filters'])) {
			foreach ($options['filters'] as $key => $filter) {
				if (isset($filter['property'])) { // полный вариант записи.
					$property_name	= $filter['property'];
					$comparison		= $filter['comparison'];
					$value			= @$filter['value'];
				} else { // сокращенный вариант записи.
					$property_name	= $filter[0];
					$comparison		= $filter[1];
					$value			= @$filter[2];
				}
				
				$filters[$property_name][] = array(
					'comparison'	=> $comparison,
					'value'			=> $value,
					);
			}
		}
		
		foreach ($filters as $property_name => $comparisons) {
			// Для каждой таблицы добавляется JOIN.
			$sql_joins["{$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_$property_name"] = "LEFT JOIN {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_$property_name AS ip$property_name USING (item_id)";
			
			// Встречается несколько условий для одного поля, по этому они завертываются в выражение OR
			$comparisons_count = count($comparisons);
			if ($comparisons_count > 1) {
				$sql_comparisons .= " AND (";
				foreach ($comparisons as $comparison) {
					if (strlen($comparison['value']) > 0) {
						$value = $this->DB->quote($comparison['value']);
					} else {
						$value = '';
					}
					$sql_comparisons .= " ip$property_name.value {$comparison['comparison']} $value \n\t";
					if ($comparisons_count-- > 1) {
						$sql_comparisons .= " OR ";
					}
				}
				$sql_comparisons .= " )\n";
			}
			// Одно условие на свойство.
			elseif ($comparisons_count == 1) {
				if (strlen($comparisons[0]['value']) > 0) {
					$value = $this->DB->quote($comparisons[0]['value']);
				} else {
					$value = '';
				}
				$sql_comparisons .= " AND ip$property_name.value {$comparisons[0]['comparison']} $value \n";
			}
		}
	
		// Сортировка.
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
			}
			unset($sql_order_by_cnt); // @todo убрать
		}
		
		$sql_joins2 = '';
		foreach ($sql_joins as $value) {
			$sql_joins2 .= $value . "\n";
		}
		
		// Если запрошен $return_items_count, то выполняется другой запрос в БД и возвращается только кол-во всех записей.
		// @todo попробовать переписать на SELECT count(i.item_id) AS items_count
		if ($return_items_count) {
			$sql = "SELECT i.item_id
				FROM {$this->prefix}items_structures_relation AS isr
				LEFT JOIN {$this->prefix}items AS i USING (item_id, entity_id)
				$sql_joins2
				WHERE isr.entity_id = '{$this->entity_id}'
				AND i.site_id = '{$this->Env->site_id}'
				$is_active
				$is_deleted
				$sql_structures
				$sql_comparisons
				GROUP BY i.item_id ";
			$result = $this->DB->query($sql);
			return $result->rowCount();
		} 
		// Получить все записи, указанного раздела.
		else {
			$sql = "SELECT i.item_id, i.uri_part, isr.category_id, i.create_datetime, i.owner_id 
				FROM {$this->prefix}items_structures_relation AS isr
				LEFT JOIN {$this->prefix}items AS i USING (item_id, entity_id)
				$sql_joins2
				WHERE isr.entity_id = '{$this->entity_id}'
				AND i.site_id = '{$this->Env->site_id}'
				$is_active
				$is_deleted
				$sql_structures
				$sql_comparisons
				GROUP BY i.item_id
				$sql_order_by
				$sql_limit ";
		}
		$result = $this->DB->query($sql);
		
		$get_item_options = array(
			'show_in_list' => 1,
			);
		if (isset($options['only_admin_properties']) and $options['only_admin_properties'] == 1) {
			$get_item_options['only_admin_properties'] = 1;
		}
		
		if (isset($options['get_structures']) and $options['get_structures'] == 1) {
			$get_item_options['get_structures'] = true;
		}
		
		while ($row = $result->fetchObject()) {
			$item = array();
			// @todo structure_id должен быть "примари".
			$structure_id = 1;
			
			$sql2 = "SELECT category_id
				FROM {$this->prefix}items_structures_relation_single
				WHERE entity_id = '{$this->entity_id}'
				AND item_id = $row->item_id
				AND structure_id = $structure_id ";
			$result2 = $this->DB->query($sql2);
			$row2 = $this->DB->getRow($sql2);
			$item['link'] = empty($row2) ? $this->getUriByCategoryId(0, 0) . $row->uri_part . $link_postfix : $this->getUriByCategoryId($structure_id, $row2['category_id']) . $row->uri_part . $link_postfix;
			$item['properties'] = $this->getItem($row->item_id, $get_item_options);
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
		$field = is_int($var) ? 'item_id' : 'uri_part';
		
		$sql = "SELECT item_id FROM {$this->prefix}items
			WHERE $field = '$var'
			AND entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			AND is_active = 1 ";
		$row = $this->DB->getRow($sql);
		return empty($row) ? false : $row['item_id'];
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
		
		$sql = "SELECT item_id, uri_part, is_active, owner_id, meta, create_datetime
			FROM {$this->prefix}items
			WHERE item_id = '$item_id'
			AND entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}' ";
		$item = $this->DB->getRow($sql);
		if (empty($item)) {
			return null;
		} else {
			$item['meta'] = unserialize($item['meta']);
		}
		
		// Формирование фрагмента SQL запроса для режимов отображения в списке и просмотр записи.
		$sql_conditions = '';
		if (isset($options['only_admin_properties']) and $options['only_admin_properties'] == 1) {
			$sql_conditions .= " AND show_in_admin = '1' ";
		} else {
			if (isset($options['show_in_list']) and $options['show_in_list'] !== 'all') {
				$sql_conditions .= " AND show_in_list = '$options[show_in_list]' ";
			}
			
			if (isset($options['show_in_view']) and $options['show_in_view'] !== 'all') {
				$sql_conditions .= " AND show_in_view = '$options[show_in_view]' ";
			}
		}
		
		// {$this->prefix}properties_translation AS pt
		// AND pt.site_id = '{$this->Env->site_id}'
		// AND pt.entity_id = p.entity_id
		// AND p.property_id = pt.property_id 
		
		// Получение списка свойств.
		$sql = "SELECT *
			FROM {$this->prefix}properties
			WHERE entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			AND is_active = 1
			$sql_conditions
			ORDER BY pos ASC ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			// Получение значений записи.
			$sql2 = "SELECT value
				FROM {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$row->name}
				WHERE item_id = '$item_id' ";
			$result2 = $this->DB->query($sql2);
			if ($result2->rowCount() == 1) {
				$row2 = $result2->fetchObject();
				
				$params = unserialize($row->params);
				
				$original_value = false;
				if ($row->type == 'img' or $row->type == 'image' or $row->type == 'file') {
					$value = $this->Media->getFileUri($row2->value, $params);
					$original_value = $row2->value;
				} elseif ($row->type == 'select') {
					if (isset($params['options'][$row2->value])) {
						$value = $params['options'][$row2->value];
					} else {
						$value = $row2->value;
					}
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
					'show_in_admin'	=> $row->show_in_admin,
					'show_in_view'	=> $row->show_in_view,
					'show_in_list'	=> $row->show_in_list,
					'empty_as_null'	=> $row->empty_as_null,
					);
			}
		}
		
		// Структуры
		if (isset($options['get_structures']) and $options['get_structures'] == 1) {
			
			$sql = "SELECT item_id, structure_id, category_id
				FROM {$this->prefix}items_structures_relation
				WHERE item_id = '$item_id'
				AND entity_id = '{$this->entity_id}' ";
			$result = $this->DB->query($sql);
			while ($row = $result->fetchObject()) {
				if ($row->structure_id == 0) {
					$item['structures'][0] = true;
					continue;
				}
				
				$sql2 = "SELECT title
					FROM " . $this->getStructureData('id', $row->structure_id, 'table') . "
					WHERE category_id = '$row->category_id' ";
				$result2 = $this->DB->query($sql2);
				$row2 = $result2->fetchObject();
				
				$item['structures'][$row->structure_id][$row->category_id] = $row2->title;
			}
			
		}
		// Категории.
		// @todo ПЕРЕДЕЛАТЬ :))
		/*
		if (isset($options['categories']) and $options['categories'] == 1) {
			$sql = "SELECT icr.category_id, ct.title
				FROM {$this->prefix}items_categories_relation AS icr,
					 {$this->prefix}categories_translation AS ct
				WHERE icr.item_id = '$item_id'
				AND icr.entity_id = '{$this->entity_id}'
				AND ct.entity_id = '{$this->entity_id}'
				AND icr.site_id = '{$this->Env->site_id}'
				AND ct.site_id = '{$this->Env->site_id}'
				AND ct.category_id = icr.category_id ";
			$result = $this->DB->query($sql);
			while ($row = $result->fetchObject()) {
				$item['categories'][$row->category_id] = $row->title;
			}
		}
		*/
		return $item;
	}
	
	/**
	 * Получить прототип записи: кол-во и свойства полей.
	 * 
	 * @param array $structures
	 * @param bool $is_admin - для админа генерируются все свойства, а не только активные.
	 * @param bool $group_by_properties_groups - по умолчанию выходной массив группируется по группым свойств.
	 * @return array
	 * 
	 * @todo добавить проверки на задействованные группы свойств, чтобы заново не собирать...
	 */
	//public function getItemPrototype($category_id = 1, $is_admin = false)
	public function getItemPrototype($structures = array(), $is_admin = false, $group_by_properties_groups = true)
	{
		$is_admin = $is_admin === false ? " AND is_active = '1' " : '';
		$prototype = array();
		
		// {$this->prefix}properties_groups_translation AS pgt
		// AND pgt.entity_id = pg.entity_id
		// AND pgt.site_id = pg.site_id
		// AND pgt.properties_group_id = pg.properties_group_id
		
		// 1. Сначала считаем все группы свойств.
		$sql = "SELECT pg.*, pg.title AS group_title, pg.name AS group_name
			FROM {$this->prefix}properties_groups AS pg
			WHERE pg.entity_id = '{$this->entity_id}'
			AND pg.site_id = '{$this->Env->site_id}'
			ORDER BY pg.pos ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			// 2. Затем считаем для каждой группы, привязана ли она к экземпляру.
			$sql2 = "SELECT * 
				FROM {$this->prefix}properties_groups_structures_relation
				WHERE site_id = '{$this->Env->site_id}'
				AND entity_id = '{$this->entity_id}'
				AND properties_group_id = '{$row->properties_group_id}'
				AND structure_id = 0  ";
			$result2 = $this->DB->query($sql2);
			if ($result2->rowCount() == 1) {
				//$row2 = $result2->fetchObject();
				// 3. Если да, то группа включается и переходим к обработке следующей группы.
				
			} else {
				// 4. Если нет, то вычисляется входит ли группы в заказанную категорию структуры.
				foreach ($structures as $key => $value) {
					// @todo 
				}
				continue;
			}
			
			// {$this->prefix}properties_translation AS pt
			// AND pt.entity_id = p.entity_id
			// AND pt.site_id = p.site_id
			// AND pt.property_id = p.property_id

			$properties = array();
			$sql2 = "SELECT * 
				FROM {$this->prefix}properties
				WHERE entity_id = '{$this->entity_id}'
				AND site_id = '{$this->Env->site_id}'
				AND properties_group_id = '{$row->properties_group_id}'
				$is_admin
				ORDER BY pos ";
			$result2 = $this->DB->query($sql2);
			while ($row2 = $result2->fetchObject()) {
				
				$properties[$row2->property_id] = array(
					'title'			=> $row2->title,
					'type'			=> $row2->type,
					'name'			=> $row2->name,
					'params'		=> $row2->params,
					'is_required'	=> $row2->is_required,
					'show_in_admin'	=> $row2->show_in_admin,
					'show_in_view'	=> $row2->show_in_view,
					'show_in_list'	=> $row2->show_in_list,
					'empty_as_null'	=> $row2->empty_as_null,
					'pos'			=> $row2->pos
					);
				if ($group_by_properties_groups == 0) {
					$properties[$row2->property_id]['group_name']  = $row->group_name;
					$properties[$row2->property_id]['group_title'] = $row->group_title;
				}
			}
			
			if ($group_by_properties_groups) {
				$prototype[$row->group_name] = array(
					'title'		 => $row->group_title,
					'properties' => $properties,
					);
			} else {
				foreach ($properties as $property_id => $property_data) {
					$prototype[$property_id] = $property_data;
				}
			}
		}
		
		return $prototype;
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
		$sql = "SELECT value FROM {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property_name} WHERE item_id = '$item_id' ";
		$row = $this->DB->getRow($sql);
		return empty($row) ? null : $row['value'];
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
		
		$data			= false;
		$breadcrumbs	= array();
		$category_pid	= 0;
		$page			= 1;
		$item_id		= false;
		$uri			= '';
		$is_success		= false;
		
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
							'path'		 => $path,
							'structures' => $category_pid == 0 ? null : array($this->structures[0]['id'] => $category_pid),
							'item_id'	 => $item_id,
							),
						'breadcrumbs'	 => $breadcrumbs,
						'title'			 => '' // @todo возможно не нужно т.к. вся инфа теперь в $breadcrumbs.
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
						'descr' => '', // @todo сделать :) хотя непонятно пока из чего оно может браться...
						);
				}
				$meta = null;
			} 
			// Если часть запроса не относится к "записи", то пробуем обработать его как "категорию".
			else {
				// @todo оптимизировать код.
				if (isset($this->structures[0]['table'])) {
					$sql = "SELECT category_id, meta, title
						FROM {$this->structures[0]['table']}
						WHERE pid = '$category_pid'
						AND uri_part = '$value'
						AND is_active = 1 ";
					$result = $this->DB->query($sql);
					if ($result->rowCount() == 1) {
						$row = $result->fetchObject();
						$uri .= $value . '/';
						$breadcrumbs[] = array (
							'uri'	=> $this->Env->current_folder_path . Site::getHttpLangPrefix() . $uri,
							'title' => $row->title,
							'descr' => '', // @todo сделать :) хотя непонятно пока из чего может браться...
							);
						$category_pid = $row->category_id;
						$meta = unserialize($row->meta);
						$is_success = true;
					} else {
						$is_success = false;
					}
				} else {
					$is_success = false;
				}
			}

		} // __end foreach $path_parts
		
		// @todo сделать мультиструктурность! пока юзается первая (rubrics)
		if ($is_success) {
			$data = array(
				'data' => array(
					'path'			=> $path,
					'structures'	=> $category_pid == 0 ? null : array($this->structures[0]['id'] => $category_pid),
					//'category_id'	=> $category_pid,
					'page' 			=> $page, // @todo проверка существует ли запрошенная страница.
					'item_id'		=> $item_id,
					'meta'			=> $meta,
					),
				'breadcrumbs' 		=> $breadcrumbs,
				);
		}
		
		return $data;
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
	 * Обновление записи.
	 * 
	 * @param array $data
	 * @return bool - успешность выполнения операции.
	 * 
	 * @todo сделать транзакцию и блокировку.
	 */
	public function updateItem($data)
	{
		if (!is_numeric($data['item_id'])) {
			return false;
		}

		// Привязка записи к структурам категорий.
		if (isset($data['structures'])) {
			$this->_updateItemsStructuresRelation($data['item_id'], $data['structures']);
		}
		
		// Обработка мета-тэгов.
		$meta = array();
		if (!empty($data['meta']['keywords'])) {
			$meta['keywords'] = $data['meta']['keywords'];
		}
		if (!empty($data['meta']['description'])) {
			$meta['description'] = $data['meta']['description'];
		}
		$meta = empty($meta) ? 'NULL' : $this->DB->quote(serialize($meta));
		
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
				AND item_id != '$data[item_id]' ";
			if ($this->DB->getRowObject($sql)->cnt > 0) {
				//$uri_part = $data['item_id'] . '-' . date('Y-m-d');
				$uri_part = trim($data['item_id']);
			}
		}
		
		// Удаление картинок.
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
							AND site_id = '{$this->Env->site_id}' ";
						if ($this->Media->deleteFile($image_id, unserialize($this->DB->getRowObject($sql)->params))) {
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
					AND name = '$key' ";
				$data['content'][$this->getPropertyId($key)] = $this->Media->createFile($_FILES[$key], unserialize($this->DB->getRowObject($sql)->params));
			}
		}
		
		$sql = "
			UPDATE {$this->prefix}items SET
				is_active = '$data[is_active]',
				uri_part = {$this->DB->quote($uri_part)},
				meta = $meta
			WHERE item_id = '$data[item_id]'
			AND entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}' ";
		$this->DB->exec($sql);

		// Обновление свойств
		foreach ($data['content'] as $key => $value) {
			$sql = "SELECT empty_as_null, name
				FROM {$this->prefix}properties
				WHERE entity_id = '{$this->entity_id}'
				AND property_id = '$key'
				AND site_id = '{$this->Env->site_id}' ";
			$row = $this->DB->getRowObject($sql);
			$property_name = $row->name;
			$empty_as_null = $row->empty_as_null;
//			$params = unserialize($row->params);
			
			// Удаление свойства, если его значение пустое.
			if (strlen(trim($value)) == 0 and $empty_as_null == 0) {
				$sql = "DELETE FROM {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property_name} WHERE item_id = '$data[item_id]' ";
				$this->DB->exec($sql);
				continue;
			}
			
			$sql = "SELECT value FROM {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property_name} WHERE item_id = '$data[item_id]' ";
			$result = $this->DB->query($sql);
			// Свойство записи есть, обновлем его.
			if ($result->rowCount() == 1) {
				if (strlen(trim($value)) == 0 and $empty_as_null == 1) {
					$sql = "
						UPDATE {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property_name} SET
							value = NULL
						WHERE item_id = '$data[item_id]' ";
					$this->DB->exec($sql);
				} else {
					$row = $result->fetchObject();
					if (md5($this->DB->quote(trim($value))) != md5($this->DB->quote(trim($row->value)))) {
						$sql = "
							UPDATE {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property_name} SET
								value = " . $this->DB->quote(trim($value)) . "
							WHERE item_id = '$data[item_id]' ";
						$this->DB->exec($sql);
					} 
				}
			// Свойства записи нет, добавляем его.
			} else {
				if (strlen(trim($value)) == 0 and $empty_as_null == 1) {
					$sql = "INSERT INTO {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property_name}
							(item_id, value) VALUES ('$data[item_id]', NULL)";
					$this->DB->query($sql);
				} else {
					$sql = "INSERT INTO {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$property_name}
							(item_id, value) VALUES ('$data[item_id]', " . $this->DB->quote(trim($value)) . " ) ";
					$this->DB->query($sql);
				}
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
		$meta = empty($meta) ? 'NULL' : $this->DB->quote(serialize($meta));

		$pid = $data['pid'];
		$is_active = $data['is_active'];

		$sql_categories_table = $this->getStructureData('id', $data['structure_id'], 'table');
		
		// uri_part не может быть произвольным числом. При обновлении категории, если юзер ввел в качестве uri_part число, то вставялется ид категории.
		if (strlen(trim($data['uri_part'])) == 0 or is_numeric(trim($data['uri_part']))) {
			$uri_part = $data['category_id'];
		} else {
			// Проверка на уникальность uri_part в пределах родительской категории.
			$Helper_Uri = new Helper_Uri();
			$uri_part = $Helper_Uri->preparePart($data['uri_part']);
			$sql = "SELECT count(category_id) AS cnt
				FROM $sql_categories_table
				WHERE uri_part = {$this->DB->quote($uri_part)}
				AND pid = '$pid'
				AND category_id != {$this->DB->quote($data['category_id'])} ";
			if ($this->DB->getRowObject($sql)->cnt > 0) {
				$uri_part = $data['category_id'];
			}
		}
		
		$sql = "
			UPDATE $sql_categories_table SET
				is_active = '$is_active',
				is_inheritance = '$data[is_inheritance]',
				pid = '$pid',
				pos = '$data[pos]',
				uri_part = '$uri_part',
				title = {$this->DB->quote(trim($data['title']))},
				meta = $meta
			WHERE category_id = '$data[category_id]' ";
		$this->DB->exec($sql);
		return true;
	}
	
	/**
	 * Получить список всех подкюченных структур.
	 *
	 * Внутри класса использовать $this->structures т.е. сборка списка структур производится в конструкторе.
	 * 
	 * @return array
	 * 
	 * @todo подсчет кол-ва записей.
	 */
	public function getStructuresList()
	{
		if ($this->is_tables_exist == false) {
			return null;
		}
		
		$sql = "SELECT structures 
			FROM {$this->prefix}entities
			WHERE entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}' ";
		if ($row = $this->DB->getRow($sql)) {
			return unserialize($row['structures']);
		} else {
			return null;
		}
	}
	
	/**
	 * Получить данные о структуре по её ID.
	 *
	 * @param string $key - имя ключа по которому надо получить значение.
	 * @param string $val
	 * @param string $field
	 * @return array|null
	 */
	public function getStructureData($key, $val, $field = false)
	{
		if ($this->isTablesExist() == false or !is_array($this->structures)) {
			return null;
		}
		
		foreach ($this->structures as $value) {
			if ($value[$key] == $val) {
				// Если не запрошено поле, то возвращаются все данные, иначе только заданное поле.
				return $field === false ? $value : $value[$field];
			}
		}
		return null;
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
		$pid = (is_numeric($data['pid']) and $data['pid'] > 0) ? $data['pid'] : 0;

		// Таблица категорий.
		$sql_cat_table = $this->getStructureData('id', $data['structure_id'], 'table');
		
		// Проверка на сущесвующую родительскую категорию. Если нет, то по умолчанию указывается родительская категория = 0.
		if ($this->DB->getRowObject("SELECT count(category_id) AS cnt FROM $sql_cat_table WHERE category_id = '$pid' ")->cnt != 1) {
			$pid = 0;
		}
		
		// Сначала создаётся категория с техническим uri_part.
		$uri_part = md5(microtime() . $this->Env->user_id . $data['uri_part']);
		
		// Вставка категории в БД.
		$sql = "
			INSERT INTO $sql_cat_table
				(title, is_active, uri_part, pos, pid, owner_id, create_datetime )
			VALUES
				($title, '$data[is_active]', '$uri_part', '$data[pos]', '$pid', '{$this->Env->user_id}', NOW() ) ";
		$this->DB->query($sql);
		$category_id = $this->DB->lastInsertId();
		
		// Далее технический uri_part заменяется на нормальный.
		// @todo ПОЧЕМУ ;) uri_part не может быть произвольным числом, при создании категории, если юзер ввел в качестве uri_part число, то вставялется произвольный хэш.
		if (strlen(trim($data['uri_part'])) == 0 or is_numeric(trim($data['uri_part']))) {
			$uri_part = $category_id;
		} else {
			$Helper_Uri = new Helper_Uri();
			$uri_part = $Helper_Uri->preparePart($data['uri_part']);
			$sql = "SELECT count(category_id) AS cnt
				FROM $sql_cat_table
				WHERE uri_part = {$this->DB->quote($uri_part)}
				AND pid = '$pid' ";
			$result = $this->DB->query($sql);
			$row = $result->fetchObject();
			if ($row->cnt > 0) {
				//$uri_part = "$item_id-" . date('Y-m-d');
				$uri_part = $category_id;
			}
			// $uri_part = $row->max_category_id . '-' . $Helper_Uri->preparePart($data['title']);
		}
		
		$sql = "
			UPDATE $sql_cat_table SET
				uri_part = {$this->DB->quote($uri_part)}
			WHERE category_id = '$category_id' ";
		$this->DB->query($sql);
		
		/*		
		$sql = "
			INSERT INTO {$this->prefix}categories_translation
				(entity_id, category_id, language_id, title, site_id )
			VALUES
				('{$this->entity_id}', '$category_id', '{$this->Env->language_id}', $title, '{$this->Env->site_id}' ) ";
		$this->DB->exec($sql);
		*/
		return true;
	}
	
	/**
	 * Добавление записи.
	 * 
	 * @uses User
	 * 
	 * @param array $data
	 * @return id|false
	 * 
	 * @todo сделать транзакцию и блокировку.
	 * @todo сейчас какая то корявость с параметром $category_id :(
	 * @todo проверку на уникальность ури_парта
	 */
	public function createItem($data)
	{
		$item_id = false;

		if (!isset($data['is_active'])) {
			$data['is_active'] = 1;
		}
		
		// Обработка мета-тэгов.
		$meta = array();
		if (!empty($data['meta']['keywords'])) {
			$meta['keywords'] = $data['meta']['keywords'];
		}
		if (!empty($data['meta']['description'])) {
			$meta['description'] = $data['meta']['description'];
		}
		$meta = empty($meta) ? 'NULL' : $this->DB->quote(serialize($meta));

		$Date = new Helper_Date();
		
		// Сначала создаётся запись с техническим uri_part.
		$uri_part = md5(microtime() . $this->Env->user_id . $data['uri_part']);
		$sql = "
			INSERT INTO {$this->prefix}items
				(entity_id, is_active, uri_part, create_datetime, owner_id, site_id, meta)
			VALUES
				('{$this->entity_id}', '$data[is_active]', '$uri_part', '" . $Date->getDatetime() . "', '{$this->Env->user_id}', '{$this->Env->site_id}', $meta ) ";
		$this->DB->query($sql);
		$item_id = $this->DB->lastInsertId();
		
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
				AND uri_part = {$this->DB->quote($uri_part)} ";
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
			AND site_id = '{$this->Env->site_id}' ";
		$this->DB->query($sql);
		
		// Привязка записи к структурам категорий.
		if (isset($data['structures'])) {
			$this->_updateItemsStructuresRelation($item_id, $data['structures']);
		} else {
			$this->_updateItemsStructuresRelation($item_id, array());
		}
		
		// Если были загружены файлы, то они добавляются в медиа хранилище.
		foreach ($_FILES as $key => $value) {
			if ($value['error'] == 0) {
				$sql = "SELECT params
					FROM {$this->prefix}properties
					WHERE entity_id = '{$this->entity_id}'
					AND name = '$key' 
					AND site_id = '{$this->Env->site_id}' ";
				$data['content'][$this->getPropertyId($key)] = $this->Media->createFile($_FILES[$key], unserialize($this->DB->getRowObject($sql)->params));
			}
		}
		
		// Заполнение контента записи.
		// @todo обработка параметров.
		foreach ($data['content'] as $key => $value) {
			$sql = "SELECT name
				FROM {$this->prefix}properties
				WHERE entity_id = '{$this->entity_id}'
				AND property_id = '$key' 
				AND site_id = '{$this->Env->site_id}' ";
			
			if (strlen($value) > 0) {
				$sql = "
					INSERT INTO {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$this->DB->getRowObject($sql)->name}
						(item_id, value)
					VALUES
						('$item_id', " . $this->DB->quote(trim($value)) . " ) ";
				$this->DB->exec($sql);
			}
		}
		
		// После вставки всех свойств записи, надо пройтись по прототипу новой записи и добавить пустые свойства.
		$prototype = $this->getItemPrototype(null, false, false);
		foreach ($prototype as $property_id => $value) {
			if ($value['empty_as_null'] == 1) {
				$sql = "SELECT count(item_id) AS cnt FROM {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$value['name']} WHERE item_id = '$item_id' ";
				if ($this->DB->getRowObject($sql)->cnt == 0) {
					$sql = "INSERT INTO {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$value['name']} (item_id, value) VALUES ('$item_id', NULL) ";
					$this->DB->exec($sql);
				}
			}
		}

		return $item_id;
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
		if ($row = $this->DB->getRow($sql)) {
			return $row['property_id '];
		} else {
			return false;
		}
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
		
		$properties_group_id = $properties_group_id === false ? '' : " AND properties_group_id = '$properties_group_id' ";
		
		// {$this->prefix}properties_groups_translation AS pgt
		// AND pgt.entity_id = '{$this->entity_id}'
		// AND pgt.site_id = '{$this->Env->site_id}'
		// AND pgt.language_id = '{$this->Env->language_id}'
		// AND pg.properties_group_id = pgt.properties_group_id
		$sql = "SELECT * 
			FROM {$this->prefix}properties_groups
			WHERE entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			$properties_group_id
			ORDER BY pos ";
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
		
		$properties_group_id = $properties_group_id === false ? '' : " AND properties_group_id = '$properties_group_id' ";
		
		$is_admin = $is_admin === false ? " AND is_active = '1' " : '';
		
		// {$this->prefix}properties_translation AS pt
		// AND pt.entity_id = '{$this->entity_id}'
		// AND pt.site_id = '{$this->Env->site_id}'
		// AND pt.language_id = '{$this->Env->language_id}'
		// AND p.property_id = pt.property_id
		$sql = "SELECT * 
			FROM {$this->prefix}properties
			WHERE entity_id = '{$this->entity_id}'
			AND site_id = '{$this->Env->site_id}'
			$properties_group_id
			$is_admin
			ORDER BY pos ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$cnt = false;
			if ($items_count) {
				$sql2 = "SELECT count(item_id) AS cnt FROM {$this->prefix}items_s{$this->Env->site_id}_e{$this->entity_id}_{$row->name} ";
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
				'empty_as_null'	=> $row->empty_as_null,
				'type'			=> $row->type,
				'params'		=> $row->params,
				'params_yaml'	=> $row->params_yaml,
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
			$sql = "DELETE FROM {$this->prefix}categories
				WHERE entity_id = '{$this->entity_id}'
				AND site_id = '{$this->Env->site_id}'
				AND category_id = '$category_id' ";
			$this->DB->exec($sql);
			$sql = "DELETE FROM {$this->prefix}categories_translation
				WHERE entity_id = '{$this->entity_id}'
				AND site_id = '{$this->Env->site_id}'
				AND category_id = '$category_id' ";
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
			AND site_id = '{$this->Env->site_id}' ";
		$this->DB->exec($sql);
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
	 * Обработчик POST данных.
	 *
	 * @param array $pd
	 * @param string $submit
	 * @return
	 */
	public function postProcessor($pd, $submit)
	{
		switch ($submit) {
			case 'create_structure':
				$this->createStructure($pd);
				cf_redirect('?structures');
				break;
			case 'update_structures':
				$this->updateStructure($pd);
				cf_redirect('?structures');
				break;
			case 'create_category':
				$this->createCategory($pd);
				cf_redirect('?structure=' . $pd['structure_id']);
				break;
			case 'delete_category':
				$this->deleteCategory($pd['category_id']);
				cf_redirect('?structure=' . $pd['structure_id']);
				break;
			case 'upadate_category':
				$this->updateCategory($pd);
				cf_redirect('?structure=' . $pd['structure_id']);
				break;
			case 'create_entity':
				$this->createEntity($pd['name'], $pd['title']);
				break;
			case 'create_tables':
				$this->createTables($pd['prefix']);
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
			case 'create_item':
				$item_id = $this->createItem($pd);
				// @todo надо редиректить на свежесозданную запись т.е. через метод getUriByItemId($item_id)
				// а еще лучше getCategoryUriByItemId($item_id) - получить ссылку на категорию в которую включена запись.
				//cf_redirect($this->getUriByCategoryId($pd['category_id'])); 
				cf_redirect($this->path_prefix);
				break;
			case 'update_item':
				$this->updateItem($pd);
				if (isset($_POST['return_to'])) {
					cf_redirect($_POST['return_to']);
				} else {
					//cf_redirect($this->getUriByCategoryId($pd['category_id']));
					cf_redirect($this->path_prefix);
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
}