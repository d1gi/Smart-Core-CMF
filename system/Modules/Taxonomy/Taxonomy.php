<?php
/**
 * Module "Таксономиия" - систематизация по тэгам.
 * 
 * @uses Node 
 * 
 * @package Module
 * @version 2011-09-03.0
 */
class Module_Taxonomy extends Module
{
	/**
	 * ИД ноды с каталогом откуда берутся тэги.
	 */
	protected $catalog_node_id;
	
	/**
	 * Кол-во записей на страницу.
	 * @var int
	 */
	protected $items_per_page;
	
	/**
	 * Имя шаблона для отображения списка записей выбраннях по заданному тегу.
	 */
	//protected $tpl;

	/**
	 * Префикс имени класса, стандартного дива.
	 * @var string
	 */
	protected $class_prefix;
	
	/**
	 * Конструктор.
	 */
	protected function init()
	{
		$this->catalog_node_id 	= $this->Node->params['catalog_node_id'];
		$this->class_prefix		= $this->Node->params['class_prefix'];
		$this->items_per_page 	= $this->Node->params['items_per_page'];
		// $this->tpl = $this->Node->params['tpl'];
	}
	
	/**
	 * Запуск модуля.
	 */
	public function run($params)
	{
		if ($this->catalog_node_id == 0) {
			return;
		}
		
		if (isset($params)) {
			if (isset($_GET['page']) and is_numeric($_GET['page'])) {
				$page_num = $_GET['page'];
			} else {
				$page_num = 1;
			}
			
			$Node = new Node($this->catalog_node_id);
			$this->output_data['requested_tag'] = $Node->hook('getTagData', array('tag_id' => $params));
			$this->output_data['items'] = $Node->hook('getItemsByTag', array('options' => array(
				'tag' => $params,
				'paginator' => array(
					'items_per_page' => $this->items_per_page,
					'current_page' => $page_num,
					),
				)));

			// Постраничность 
			$this->output_data['pages'] = new Helper_Paginator(array(
					'items_count' => $Node->hook('getItemsCount', array('options' => array(
						'tag' => $params,
						))),
					'items_per_page' => $this->items_per_page,
					'current_page' => $page_num,
					'link_tpl' => '?page={PAGE}',
					)
				);
		} else {
			// Отобразить облако тегов.
			$this->setTpl('TagsCloud');
			$this->output_data['tags'] = $this->hook('getTagsCloud');
		}
		
		// Префикс CSS классов
		$this->output_data['class_prefix'] = $this->class_prefix;
		
		//$this->output_data['tags'] = $Node->hook('getTagsCloud');
	}	

	/**
	 * Обработчик хуков.
	 *
	 * @param string $method - имя вызываемого метода.
	 * @param array $args - массив с аргументами.
	 */
	public function hook($method, $args = false)
	{
		$Node = new Node($this->catalog_node_id);
		switch ($method) {
			case 'getTagsCloud':
				$tags = $Node->hook('getTagsCloud');
				$path = Folder::getUri($this->Node->folder_id);
				$tmp = array();
				foreach ($tags as $value) {
					$tmp[] = array(
						'title' => $value['title'],
						'weight' => $value['items_count'],
						'params' => array('url' => $path . $value['name']),
						);
				}
					
				$cloud = new Zend_Tag_Cloud(array(
					'tags' => $tmp,
					'cloudDecorator' => array(
						'decorator' => 'HtmlCloud',
						'options' => array('htmlTags' => array (
								'div' => array ('id' => 'tags')),
								'separator' => ' ' )    
						), 
					'tagDecorator' => array (
						'decorator' => 'HtmlTag',
						'options' => array (
							'htmlTags' => array ('span'),
							)
						)
					));
				return $cloud;
				break;
			default;
		}
		return true;
	}
	
	/**
	 * Парсер части УРИ.
	 * 
	 * @param string $path - часть URI запроса
	 * @return array|false
	 */
	public function parse($path)
	{
		$tag = explode('/', $path);
		$tag = $tag[0];
		
		$Node = new Node($this->catalog_node_id);
		
		$data = array(
			'data' => $Node->hook('getTagId', array('name' => $tag)),
			);
		
		if (!empty($data['data'])) {
			$tag = $Node->hook('getTagData', array('tag_id' => $data['data']));
			$this->Breadcrumbs->add(Folder::getUri($this->Node->folder_id) . $tag['name'] . '/', $tag['title']);

			if (isset($_GET['page']) and is_numeric($_GET['page'])) {
				$this->Breadcrumbs->add(Folder::getUri($this->Node->folder_id) . $tag['name'] . '/', 'Страница № ' . $_GET['page']);
			}
		}
		
		return $data;
	}
}