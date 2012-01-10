<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Модуль Menu.
 * 
 * @package Module
 * 
 * @uses Kernel
 * @uses Permissions
 * 
 * @version 2011-11-01.0
 */
class Module_Menu extends Module
{
	protected $menu_group_id;
	protected $max_depth;
	protected $css_class;
	protected $tpl;

	protected $_tree_level = 0;
	protected $_folder_tree_list_arr = array();
//	protected $tree_link = array();
	
	protected $only_is_active = true;
	protected $selected_inheritance = true;
	
	/**
	 * Конструктор.
	 * 
	 * @return void
	 */
	protected function init()
	{
		$this->Node->setDefaultParams(array(
			'menu_group_id'	=> 0,
			'max_depth'		=> 0,
			'css_class'		=> '',
			'selected_inheritance' => 0,
			'tpl' 			=> '',
			));

		$this->menu_group_id 			= $this->Node->getParam('menu_group_id');
		$this->selected_inheritance		= $this->Node->getParam('selected_inheritance');
		$this->css_class				= $this->Node->getParam('css_class');
		$this->max_depth				= $this->Node->getParam('max_depth');
		$this->tpl						= $this->Node->getParam('tpl');
	}

	/**
	 * Запуск модуля.
	 * 
	 * @return void
	 */
	public function run($parser_data)
	{
		if (!empty($this->tpl)) {
			$this->setTpl($this->tpl);
		}
		
		$this->_getTreeList($this->getTree(0, $this->max_depth));
		$this->output_data['css_class'] = $this->css_class;
		$this->output_data['items'] = $this->_folder_tree_list_arr;
//		$this->output_data['items'] = $this->getItems();
	}	

	/**
	 * Получить параметры кеширования модуля.
	 * 
	 * @access public
	 * @return array $params
	 */
	public function getCacheParams($cache_params = array())
	{
		$params = parent::getCacheParams($cache_params);
		
		// Зависимости от папок: все пункты меню, которые ссылаются на папки.
		$params['folders'] = array();
		$sql = "SELECT folder_id
			FROM {$this->DB->prefix()}menu_items
			WHERE site_id = '{$this->Env->site_id}'
			AND is_active = '1'
			AND folder_id > '0'
			AND group_id = '{$this->menu_group_id}' ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$params['folders'][$row->folder_id] = 1;
		}
		
		return $params;
	}
	
	/**
	 * получаем древовидный список элементов меню.
	 * 
	 * @uses Kernel
	 * @uses Permissions
	 * 
	 * @todo Сделать поддержку древовидности (вложенных пунктов меню)
	 */
	protected function getItems_old($pid = false, $max_depth = false)
	{
		$items = array();
		$sql = "SELECT
				i.item_id,
				i.is_active,
				i.pos,
				i.pid,
				i.folder_id,
				i.suffix,
				i.direct_link,
				i.title,
				i.descr,
				i.options,
				f.permissions,
				t.title AS folder_title,
				t.descr AS folder_descr
			FROM {$this->DB->prefix()}menu_items AS i
			LEFT JOIN {$this->DB->prefix()}engine_folders AS f USING (folder_id)
			LEFT JOIN {$this->DB->prefix()}engine_folders_translation AS t USING (folder_id)
			WHERE i.group_id = '$this->menu_group_id'
				AND i.is_active = 1
				AND f.is_active = 1
				AND t.language_id = '{$this->Env->language_id}'
			ORDER BY i.pos ";
		$result = $this->DB->query($sql);
		while($row = $result->fetchObject()) {
			// проверяем возможность на чтение и просмотр папки.
			if ($this->Permissions->isAllowed('folder', 'read', $row->permissions) == 0 or $this->Permissions->isAllowed('folder', 'view', $row->permissions) == 0) {
				continue; //echo "$row->folder_title";	
			}

			$items[$row->item_id]['selected'] = 0;
			$items[$row->item_id]['uri'] = Folder::getUri($row->folder_id);
			$items[$row->item_id]['title'] = $row->folder_title;
			$items[$row->item_id]['descr'] = $row->folder_descr;
//			$items[$row->item_id]['folder_id'] = $row->folder_id;
			$items[$row->item_id]['options'] = unserialize($row->options);
//			$items[$row->item_id]['_temp_group_id'] = $group_id;
//			$items[$row->item_id]['items'] = array(); // $this->getItems($row->item_id, $max_depth);
//			$items[$row->item_id]['pid'] = $row->pid;
		}
		return $items;
	}
	
	/**
	 *  
	 */
	public function getMenuGroupsListArr($selected = 1, $domain_id = false)
	{
		$data = array();
		$sql = "SELECT * FROM {$this->DB->prefix()}menu_groups ";
		$result = $this->DB->query($sql);
		while($row = $result->fetchObject()) {
			$data[$row->group_id]['title'] = "$row->descr ($row->name)";
			$data[$row->group_id]['level'] = 0;
			if ($row->group_id == $selected) {
				$tmp = 1;
			} else {
				$tmp = 0;
			}
			$data[$row->group_id]['selected'] = $tmp;
		}
		return $data;
	}
	
	
	/**
	 * Обработчик POST данных
	 * 
	 * @param int $pd
	 * @param string $submit
	 * @return void
	 */
	public function postProcessor($pd, $submit)
	{
		switch ($submit) {
			case 'save':
				$this->updateItem($pd);
				break;
			case 'create':
				$this->createItem($pd);
				break;
			case 'delete':
				$this->deleteItem($pd);
				break;
			default:
		}
	}
	
	/**
	 * Получить плоский список пунктов меню для формирования хтмл списков.
	 * 
	 * Рекурсия.
	 */
	protected function _getTreeList($items)
	{
		foreach ($items as $key => $value) {
			$this->_folder_tree_list_arr[$key]['level'] = $this->_tree_level;
			$this->_folder_tree_list_arr[$key]['uri'] = $value['uri'];
			$this->_folder_tree_list_arr[$key]['title'] = $value['title'];
			$this->_folder_tree_list_arr[$key]['descr'] = $value['descr'];
			$this->_folder_tree_list_arr[$key]['options'] = $value['options'];
			$this->_folder_tree_list_arr[$key]['pos'] = $value['pos'];
			$this->_folder_tree_list_arr[$key]['is_active'] = $value['is_active'];
			$this->_folder_tree_list_arr[$key]['selected'] = $value['selected'];
			/*
			if ($cur_folder_id == $value['folder_id']) {
				$this->folder_tree_list_arr[$value['folder_id']]['selected'] = 1;
			} else {
				$this->folder_tree_list_arr[$value['folder_id']]['selected'] = 0;
			}
			*/
			if (count($value['items']) > 0) {
				$this->_tree_level++;
				$this->_getTreeList($value['items']);
			}
		}
		$this->_tree_level--;
	}
	
	/**
	 * Получить дерево пунктов меню.
	 * 
	 * @uses Kernel
	 * @uses Permissions
	 * 
	 * @param int $parent_id
	 * @param int $max_depth
	 * @return array
	 */
	public function getTree($parent_id, $max_depth = false)
	{ 
		$this->_tree_level++;
		$items = array();

		if ($this->only_is_active) {
			$is_active = 'AND i.is_active = 1';
		} else {
			$is_active = '';
		}
		
		// @todo сделать через класс Folder
//		$Folder = new Folder();
		
//		$folder = $Folder->getData($folder_name, $folder_pid);
		
		// LEFT JOIN {$this->DB->prefix()}engine_folders_translation AS ft USING (folder_id)
		// AND ft.site_id = '{$this->Env->site_id}'
		// AND ft.language_id = '{$this->Env->language_id}'
		$sql = "SELECT i.item_id, i.is_active, i.pos, i.pid, i.folder_id, i.suffix, i.direct_link, i.title, i.descr, i.options, f.permissions,
				f.title AS folder_title, f.descr AS folder_descr
			FROM {$this->DB->prefix()}menu_items AS i
			LEFT JOIN {$this->DB->prefix()}engine_folders AS f USING (folder_id)
			WHERE i.group_id = '$this->menu_group_id'
				$is_active
				AND i.site_id = '{$this->Env->site_id}'
				AND f.site_id = '{$this->Env->site_id}'
				AND f.is_active = 1
				AND i.pid = $parent_id
			ORDER BY i.pos ";
		$result = $this->DB->query($sql);
		while($row = $result->fetchObject()) {
			// проверяем возможность на чтение и просмотр папки.
			if ($this->Permissions->isAllowed('folder', 'read', $row->permissions) == 0 or $this->Permissions->isAllowed('folder', 'view', $row->permissions) == 0) {
				continue; //echo "$row->folder_title";	
			}
			
			// копаем до указанной глубины.
			if ($max_depth != false and $max_depth < $this->_tree_level) {
				continue;
			}

			if (empty($row->direct_link)) {
				$uri = Folder::getUri($row->folder_id);
			} else {
				$uri = $row->direct_link;
			}
			
			if (empty($row->title)) {
				$title = $row->folder_title;
			} else {
				$title = $row->title;
			}
			
			$selected = 0;
			if ($this->selected_inheritance) {
				foreach ($this->EE->breadcrumbs as $breadcrumb) {
					if ($breadcrumb['uri'] === $uri and ($uri != HTTP_ROOT or Kernel::getEnv()->current_folder_id == 1)) {
						$selected = 1;
						break;
					}
				}
			} else {
				if (Kernel::getEnv()->current_folder_id == $row->folder_id) {
					$selected = 1;
				}
			}
			
			$items[$row->item_id] = array(
				'selected' => $selected,
				'uri' => $uri,
				'title' => $title,
				'descr' => $row->folder_descr,
				'folder_id' => $row->folder_id,
				'options' => unserialize($row->options),
				// '_temp_group_id' => $group_id,
				'pid' => $row->pid,
				'pos' => $row->pos,
				'is_active' => $row->is_active,
				'items' => $this->getTree($row->item_id, $max_depth),
				);
			
		} // end while $row
		
		$this->_tree_level--;
		return $items;
	}
 
}