<?php
/**
 * Глобальный объект с данными (Essential Elements)
 * 
 * Коллектор данных для шаблонизатора (Essential Elements), включает инструкции для формирования шаблона.
 * 
 * @package		Kernel
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF* 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @uses		ScriptsLib
 * @uses		Uri
 * 
 * @version		2012-01-09.0
 */
final class EE extends Singleton
{
	/**
	 * Список нод, которые не должно собираться.
	 * @var array
	 */
	private $_disabled_nodes = array();
	
	/**
	 * Список всех запрошенных скриптов.
	 * @var
	 */
	private $_requested_libs = array();
	
	//private $_head_links = array();
	private $_head_scripts = array();
	private $_head_styles = array();

	/**
	 * Хлебные крошки
	 * @var array
	 */
	public $breadcrumbs = array();
	
	/**
	 * Данные для администрирования
	 * @var array
	 */
	public $admin = array();
	
	/**
	 * Данные для секции <head>
	 * @var array
	 */
	public $head = array();
	
	/**
	 * Информация для шаблонизатора.
	 * @var array
	 */
	public $template = array();
	
	/**
	 * Данные нод.
	 * @var array
	 * 
	 * @todo переименовать в $nodes_data
	 */
//	public $data = array();
	
	/**
	 * Отключение ноды.
	 *
	 * @param int $node_id
	 */
	public function disableNode($node_id)
	{
		$this->_disabled_nodes[$node_id] = true;
	}
	
	/**
	 * Включение ноды.
	 *
	 * @param int $node_id
	 */
	public function enableNode($node_id)
	{
		$this->_disabled_nodes[$node_id] = false;
	}
	
	/**
	 * Подготовка данных, в частности удаляет запрошенные ноды для удаления.
	 */
	public function preparation()
	{
		// @todo ВАЖНО! продумать как, удалять ноды!
		if (isset($this->data)) {
			foreach ($this->data as $container => $nodes) {
				foreach ($this->_disabled_nodes as $node_id => $is_delete) {
					if (($is_delete === true or $is_delete === 1) and array_key_exists($node_id, $nodes)) {
						unset($this->data[$container][$node_id]);
					}
				}
			}
		}

		// Удаление пустых мета тэгов.
		foreach ($this->head['meta'] as $name => $meta) {
			foreach ($meta as $key => $value) {
				if (empty($value)) {
					unset($this->head['meta'][$name][$key]);
				}
			}
		}
		
		// Сборка строки <title> из "хлебных крошек".
		$title = '';
		$breadcrumbs = $this->breadcrumbs;
		krsort($breadcrumbs);
		foreach ($breadcrumbs as $key => $value) {
			if ($key == 0) {
				break;
			}
			$title .= $value['title'] . ' / '; // @todo сделать настройку разделителя.
		}
		
		// Если "хлебных крошек" нет, то отображаем полное имя сайта, иначе сокращенное.
		if (count($breadcrumbs) > 1) {
			$title .= $this->head['site_short_name'];
		} else {
			$title .= $this->head['site_full_name'];
		}
		
		$this->head['title'] = $title;
		
		// Подключение JS кода для события document-ready.
		if (isset($this->head['document-ready']) and !empty($this->head['document-ready'])) {
			$this->useScriptLib('jquery');
		}
		
		// Подключение запрошенных библиотек.
		$ScriptsLib = Registry::get('ScriptsLib');
		foreach ($ScriptsLib->get($this->_requested_libs) as $name => $value) {
			if (isset($value['js'])) {
				foreach ($value['js'] as $path) {
					$this->head['script'][$name]['src'] = $path;
				}
			}
			
			if (isset($value['css'])) {
				foreach ($value['css'] as $path) {
					$this->head['style'][$name]['href'] = $path;
				}
			}
		}

		// Подключение запрошенных скриптов
		foreach ($this->_head_scripts as $name => $value) {
			$this->head['script'][$name] = $value;
		}
		
		// Подключение запрошенных стилей
		foreach ($this->_head_styles as $name => $value) {
			$this->head['style'][$name] = $value;
		}
	}
	
	/**
	 * Добавить хлебную крошку.
	 * 
	 * @uses Uri
	 * 
	 * @param string $uri
	 * @param string $title
	 * @param string $descr
	 *
	public function addBreadCrumb($uri, $title, $descr = false)
	{
		if (!Uri::isAbsolute($uri)) {
			$uri = $this->breadcrumbs[count($this->breadcrumbs) - 1]['uri'] . $uri;
		}
		
		$this->breadcrumbs[] = array(
			'uri'	=> $uri,
			'title' => $title,
			'descr' => $descr,
			);
	}
	*/
	
	/**
	 * Подключение библиотечных скриптов.
	 *
	 * @param string $name
	 * @param string $version
	 */
	public function useScriptLib($name, $version = false)
	{
		$this->_requested_libs[$name] = $version;
	}

	/**
	 * Добавить произвольные данные в <head>.
	 *
	 * @param text $data
	 */
	public function addHeadData($data)
	{
		$this->head['_data'][] = $data;
	}
	
	/**
	 * Добавить данные для тега <script>.
	 *
	 * Формат массива с параметрами: (каждый из них не является обязательным)
	 *  - src  - добавляет аттрибут src="".
	 *  - data - вставляет код между тегами <script> и </script>.
	 *  - type - указать тип. по умолчанию  'text/javascript'.
	 * 
	 * @param string $name - техническое уникальное имя (id)
	 * @param array $params - массив с параметрами
	 * @param int $pos - позиция (чем больше, чем раньше подключится)
	 */
	public function addHeadScript($name, $params, $pos = 0)
	{
		if (is_array($params)) {
			if (isset($params['src']) and !empty($params['src'])) {
				$this->_head_scripts[$name]['src'] = $params['src'];
			}
			if (isset($params['data']) and !empty($params['data'])) {
				$this->_head_scripts[$name]['data'] = $params['data'];
			}

//			$this->_head_scripts[$name]['src'] = $params['src'];
		} else {
			$this->_head_scripts[$name]['src'] = $params;
		}
	}
	
	/**
	 * Добавить данные для тега <style>.
	 *
	 * Формат массива с параметрами: (каждый из них не является обязательным)
	 *  - href  - добавляет аттрибут href="".
	 *  - data - вставляет код между тегами <style> и </style>.
	 *  - media - медиа :)
	 *  - type - указать тип. по умолчанию  'text/css'.
	 * 
	 * @param string $name - техническое уникальное имя (id)
	 * @param array $params - массив с параметрами
	 * @param int $pos - позиция (чем больше, чем раньше подключится)
	 */
	public function addHeadStyle($name, $params, $pos = 0)
	{
		if (is_array($params)) {
			if (isset($params['href']) and !empty($params['href'])) {
				$this->_head_styles[$name]['href'] = $params['href'];
			}
			if (isset($params['data']) and !empty($params['data'])) {
				$this->_head_styles[$name]['data'] = $params['data'];
			}
			if (isset($params['ie']) and !empty($params['ie'])) {
				$this->_head_styles[$name]['ie'] = $params['ie'];
			}
		} else {
			$this->_head_styles[$name]['href'] = $params;
		}
	}
	
	/**
	 * Добавить JS код, который должен быть исполнен при событии document-ready'.
	 * 
	 * Метод автоматически подключает либу jquery.
	 *
	 * @param text $js_code
	 */
	public function addDocumentReady($js_code)
	{
		$this->head['document-ready'][] = $js_code;
	}
	
	/**
	 * Добавить мета тэг.
	 *
	 * @param string $name
	 * @param string $content
	 * @param string $type (name, http-equiv, property)
	 */
	public function addHeadMeta($name, $content, $type = 'name')
	{
		$this->head['meta'][$type][$name] = $content;
	}
	
	// ---------------------------------------------------------------------------------------
	// @todo Надо реализовать следующие методы.
	
	/**
	 * Получить путь к либе.
	 * 
	 * @param string $name
	 * @return string
	 */
	public function getScriptPath($name, $version = false)
	{
	}	

	/**
	 * Добавить данные для тега <link>.
	 *
	 * Формат массива с параметрами: (каждый из них не является обязательным)
	 *  - rel  - добавляет аттрибут rel="".
	 *  - href - добавляет аттрибут rel="".
	 *  - type - добавляет аттрибут type="".
	 *  - charset - добавляет аттрибут charset="".
	 *  - media - добавляет аттрибут media="".
	 *  - sizes - добавляет аттрибут sizes="".
	 * 
	 * @param string $name - техническое уникальное имя (id)
	 */
	public function addHeadLink($name, array $params)
	{
		$this->head['link'][$name] = $params;
	}
}