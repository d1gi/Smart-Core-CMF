<?php
/**
 * HTML.
 * 
 * @author		Artem Ryzhkov
 * @category	System
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version		2012-02-01.0
 */
class Html extends View
{
	/**
	 * Doctype.
	 */
	static public $doctype = 'XHTML1_STRICT';
	
	/**
	 * Данные для секции <head>
	 * @var array
	 */
	static protected $_head = array(
		'title'		=> false,
		'meta'		=> array(),
		'styles'	=> array(),
		'scripts'	=> array(),
		'_data'		=> array(),
		'document-ready'	=> array(),
		);
	
	// @todo пересмотреть.
	protected $frontend_mode;
	public $admin = array(
		'frontend' => array(),
		);
	
	/**
	 * Атрибуты тэга <body>
	 */
	protected $body_attributes = array();
	
	/**
	 * Данные для шаблонизатора.
	 * @var array
	 * 
	 * @todo пересмотреть! может быть лучше сделать через class Template extends Html.
	 */
	protected $template = array(
		'views'	=> false, // @todo Убрать!
		'http_theme_root' => false,
		);
	
	/**
	 * Получить данные секции <head>.
	 */
	public function getHeadData()
	{
		return self::$_head;
	}
	
	/**
	 * Добавить произвольные данные в <head>.
	 *
	 * @param text $data
	 */
	public function addHeadData($data)
	{
		self::$_head['_data'][] = $data;
	}
	
	/**
	 * Установить атрибут тэга <body>.
	 */
	public function setBodyAttribute($key, $value)
	{
		$this->body_attributes[$key] = $value; 
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
		self::$_head['meta'][$type][$name] = $content;
	}
	
	/**
	 * Добавить мета тэг name.
	 *
	 * @param string $name
	 * @param string $content
	 */
	public function addHeadMetaName($name, $content)
	{
		$this->addHeadMeta($name, $content, 'name');
	}
	
	/**
	 * Добавить мета тэг http-equiv.
	 *
	 * @param string $name
	 * @param string $content
	 */
	public function addHeadMetaHttpEquiv($name, $content)
	{
		$this->addHeadMeta($name, $content, 'http-equiv');
	}
	
	/**
	 * Добавить мета тэг property.
	 *
	 * @param string $name
	 * @param string $content
	 */
	public function addHeadMetaProperty($name, $content)
	{
		$this->addHeadMeta($name, $content, 'property');
	}
	
	/**
	 * Добавить данные для тега <script>.
	 *
	 * Формат массива с параметрами: (каждый из них не является обязательным)
	 *  - src  - добавить аттрибут src="".
	 *  - data - вставить код между тегами <script> и </script>.
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
				self::$_head['scripts'][$name]['src'] = $params['src'];
			}
			
			if (isset($params['data']) and !empty($params['data'])) {
				self::$_head['scripts'][$name]['data'] = $params['data'];
			}

//			self::$_head['scripts'][$name]['src'] = $params['src'];
		} else {
			self::$_head['scripts'][$name]['src'] = $params;
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
	public function addHeadStyle($name, $params, $pos = 0) // @todo pos
	{
		if (is_array($params)) {
			if (isset($params['href']) and !empty($params['href'])) {
				self::$_head['styles'][$name]['href'] = $params['href'];
			}
			if (isset($params['data']) and !empty($params['data'])) {
				self::$_head['styles'][$name]['data'] = $params['data'];
			}
			if (isset($params['ie']) and !empty($params['ie'])) {
				self::$_head['styles'][$name]['ie'] = $params['ie'];
			}
		} else {
			self::$_head['styles'][$name]['href'] = $params;
		}
	}
	
	/**
	 * Добавить JS код, который должен быть исполнен при событии document-ready.
	 * Метод автоматически подключает либу jquery.
	 *
	 * @param text $js_code
	 */
	public function addDocumentReady($js_code)
	{
		self::$_head['document-ready'][] = $js_code;
	}
	
	/**
	 * Генерируется тект JS функции для отрисовки фронт-енд контролсов.
	 * 
	 * @todo !!! ПЕРЕДЕЛАТЬ! лучше убрать вообще из этого класса, а создать что-то вроде Admin_Frontend
	 */
	private function _generateFrontControls()
	{
		$tmp = 'function frontControls() {';
		
		foreach ($this->admin['frontend'] as $key => $value) {
//			$tmp .= "\naddFrontControl2('#_node$key', '$key');\n";
//			$tmp .= '$j("#cmf-draggable_node' . $key . '").draggable({cancel: "div.cmf-draggable-split-menu-body", revert: false});' . "\n";
			
			// Отображаем фронтенды ноды
			$tmp .= "
		addFrontControl('#_node$key', '$key', [";

			foreach ($value['controls'] as $key2 => $value2) {
				$tmp .= "{
			frame_title: '$value2[popup_window_title]',
			link: '$value2[link]', 
			title: '$value2[title]', 
			ico: '$value2[ico]',
			node_action_mode: '$value[node_action_mode]'
		},";
			}
			
			$tmp .= '
		]);
	';

			// Отображаем фронтенды внутрених блоков ноды
			if ($value['controls_inner'] !== false) {
				foreach ($value['controls_inner'] as $key2 => $value2) {
					$tmp .= "
			addFrontControl('#$key2', '$key2', [";
					foreach ($value2 as $key3 => $value3) {
						$tmp .= "{
					frame_title: '$value3[popup_window_title]',
					link: '$value3[link]', 
					title: '$value3[title]', 
					ico: '$value3[ico]',
					node_action_mode: '$value[node_action_mode]'
				},";
					}

					$tmp .= '
			]);
			';
				}
			}
		}
	
		return $tmp . '}';
	}
	
	/**
	 * Установить значение тэга <title>
	 * 
	 * @param $title
	 */
	public function setTitle($title)
	{
		self::$_head['title'] = $title;
	}

	/**
	 * Отрисовка документа.
	 */
	public function render()
	{		
		$this->frontend_mode = true;
		if (Registry::get('Cookie')->cmf_frontend_mode === 'view') { // @todo ненравится мне этот момент...
			$this->frontend_mode = false;
		}
		
		// Чтение конфига темы.
		if (file_exists(parent::$__paths[0] . 'theme.ini')) {
			$theme_ini= parse_ini_file(parent::$__paths[0] . 'theme.ini');
		}

		// Чтение ini-шника макета.
		if (file_exists(parent::$__paths[0] . 'layouts/' . $this->__options['tpl_name'] . '.ini')) {
			$layout_ini = parse_ini_file(parent::$__paths[0] . 'layouts/' . $this->__options['tpl_name'] . '.ini');
			// Отображать ли фронт-админку.
			$this->frontend_mode = (isset($layout_ini['front_controls']) and $layout_ini['front_controls'] == true and $this->frontend_mode) ? true : false;
		}
		
		self::$doctype	 = isset($theme_ini['doctype'])	? $theme_ini['doctype']		: 'XHTML1_STRICT';
		$css_path	 = isset($theme_ini['css_path'])	? $theme_ini['css_path']	: 'css/';
		$images_path = isset($theme_ini['images_path'])	? $theme_ini['images_path']	: 'images/';
		$js_path	 = isset($theme_ini['js_path'])		? $theme_ini['js_path']		: 'js/';
		
		define('HTTP_THEME',		$this->template['http_theme_root']); 
		define('HTTP_THEME_CSS',	$this->template['http_theme_root'] . $css_path);
		define('HTTP_THEME_IMAGES',	$this->template['http_theme_root'] . $images_path);
		define('HTTP_THEME_JS',		$this->template['http_theme_root'] . $js_path);
	
		$this->addHeadMetaHttpEquiv('Content-Type',  'text/html; charset=utf-8');
		$this->addHeadMetaHttpEquiv('Content-Language', 'ru');
		
		$ScriptsLib	= Registry::get('ScriptsLib');		
		// В случае, если есть фронт-енд элементы управления, добавляем ресурсы и генерируем JS скрипт.
//		if (isset(Registry::get('EE')->admin['frontend']) and count(Registry::get('EE')->admin['frontend']) > 0  and $this->frontend_mode) {
		if (count($this->admin['frontend']) > 0 and $this->frontend_mode) {
			$ScriptsLib->request('jquery');

			$this->addHeadStyle('frontend-ie', array('href' => HTTP_SYS_RESOURCES . 'admin/frontend/node_control-ie.css', 'ie' => 'lt IE 8')); // @todo продумать как подключать стили для ИЕ.
			$this->addHeadStyle('frontend',  HTTP_SYS_RESOURCES . 'admin/frontend/node_control.css');
			$this->addHeadScript('frontend', HTTP_SYS_RESOURCES . 'admin/frontend/front_controls2.js');
			$this->addHeadScript('_generateFrontControls', array('data' => $this->_generateFrontControls()));
			$this->addDocumentReady('frontControls();');
			
			// Подключается скрипт всплывающих окон.
			// @todo как-то некрасиво ;)) 
			// @todo разобраться, почему Component_Popup надо подключать после отображения фронтенд секции, иначе lightview не срабатывает.
			$Popup = new Component_Popup();
		}

		// Продолжение обработки ini-шника макета.
		if (isset($layout_ini)) {
			// Подключение CSS стилей.
			if (isset($layout_ini['css'])) {
				$scripts = explode(',', $layout_ini['css']);
				foreach ($scripts as $script_name) {
					$this->addHeadStyle(trim($script_name), HTTP_THEME_CSS . trim($script_name));
				}
			}
			
			// Подключение JS-библиотек.
			if (isset($layout_ini['js_lib'])) {
				$scripts = explode(',', $layout_ini['js_lib']);
				foreach ($scripts as $script_name) {
					$ScriptsLib->request(trim($script_name));
				}
			}
		
			// Подключение JS скриптов макета.
			if (isset($layout_ini['js'])) {
				$scripts = explode(',', $layout_ini['js']);
				foreach ($scripts as $script_name) {
					$this->addHeadScript("layout script $script_name", HTTP_THEME_JS . trim($script_name));
				}
			}
			
			// Отображать ли toobar.
			$this->show_toobar = (isset($layout_ini['toolbar']) and $layout_ini['toolbar'] == true) ? true : false;
		}

										$Permissions = Registry::get('Permissions');
										// @todo ВАЖНО! сделать адекватное подключение тулбара. сейчас отображается только для групп рута и админа.
										if (($Permissions->isAdmin() or $Permissions->isRoot()) and $this->show_toobar and $this->layout !== 'blank') {
											$this->Toolbar = new Admin_Toolbar();
										}
										unset($Permissions);

		$this->doctype();	// в нём же генерация открытия тега <html> с аргументами для доктайпа.
		$this->head();		// декораторы <head> и </head> прямо в нём.
		$this->body();	
	}
	
	/**
	 * Метод отрисовки данных блоков, вызывается из шаблонов т.е. представлений (views). 
	 * 
	 * Формат массива $options:
	 * - class - имя класса.
	 * - id - id тега.
	 * - before - код предшествующий выводу шаблона в случае, если блок не пустой.
	 * - after - код последующий выводу шаблона в случае, если блок не пустой.
	 * 
	 * @param string $block
	 * @param string $start_tag
	 * @param string $end_tag
	 * @param array $options
	 * @return bool
	 */
	protected function block($block, $options = false) // @todo $options
	{
		if (is_object($this->Blocks->$block) and method_exists($this->Blocks->$block, 'render')) {
			$this->Blocks->$block->render();
		} else {
			echo $this->Blocks->$block;
		}
	}
	
	/**
	 * NewFunction
	 */
	public function setTemplateViews($views)
	{
		$this->template['views'] = $views; // @todo Убрать!
	}
	
	/**
	 * NewFunction
	 */
	public function setTemplateHttpThemeRoot($path)
	{
		$this->template['http_theme_root'] = $path; // @todo Убрать!
		$this->__options['http_theme_root'] = $path;
	}
	
	/**
	 * Представление (view) блоков внутри макетов (layout).
	 * 
	 * @param string $name
	 * @param bool $force
	 * @return bool
	 * 
	 * @todo Убрать!
	 */
	protected function view($name, $force = false)
	{
		$paths = parent::getPaths();
		$views = $this->template['views'];
		
		$tmp = null;
		if ($force) {
			$tmp = $paths[0] . 'views/' . $view . '.tpl';
		} elseif (isset($views[$name])) {
			$view = $views[$name];
			$tmp = $paths[0] . 'views/' . $view . '.tpl';
		}
		
		if (file_exists($tmp)) {
			include $tmp;
			return true;
		}
		
		return false;	 	
	}

	/**
	 * NewFunction
	 */
	public function doctype()
	{
		// в нём же генерация открытия тега <html> с аргументами для доктайпа.
		
		$doctype = '';
		switch (self::$doctype) {
			case 'XHTML11':
				$doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
				$doctype .= "\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemalocation=\"http://www.w3.org/1999/xhtml http://www.w3.org/MarkUp/SCHEMA/xhtml11.xsd\" xml:lang=\"{$this->content_language}\" lang=\"{$this->content_language}\" dir=\"ltr\">";
				break;
			case 'XHTML1_STRICT':
				$doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
				$doctype .= "\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$this->content_language}\" lang=\"{$this->content_language}\" dir=\"ltr\">";
				break;
			case 'XHTML1_TRANSITIONAL':
				$doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
				$doctype .= "\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$this->content_language}\" lang=\"{$this->content_language}\" dir=\"ltr\">";
				break;
			case 'XHTML1_FRAMESET':
				$doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
				$doctype .= "\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$this->content_language}\" lang=\"{$this->content_language}\" dir=\"ltr\">";
				break;
			case 'XHTML1_RDFA':
				$doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">';
				$doctype .= "\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$this->content_language}\" lang=\"{$this->content_language}\" dir=\"ltr\">";
				break;
			case 'XHTML_BASIC1':
				$doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">';
				$doctype .= "\n<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"{$this->content_language}\" lang=\"{$this->content_language}\" dir=\"ltr\">";
				break;
			case 'HTML4_LOOSE':
			case 'HTML4_TRANSITIONAL':
				$doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
				$doctype .= "\n<html>";
				break;
			case 'HTML4_STRICT':
				$doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
				$doctype .= "\n<html>";
				break;
			case 'HTML4_FRAMESET':
				$doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
				$doctype .= "\n<html>";
				break;
			case 'HTML5':
			case 'XHTML5':
				$doctype = '<!DOCTYPE html>';
				$doctype .= "\n<html>";
				break;
			default;
		}
		
		echo $doctype;
	}
	
	/**
	 * Отрисовка секции <head>
	 */
	public function head()
	{
		$head = "\n<head>\n";
		
		// @todo вынести код для title, а оставить только это: $head .= "\t<title>" . self::$_head['title'] . "</title>\n";
		include DIR_SYSTEM . '_temp/html_head_title.php';
		
		// Тэги <link>.
		if (isset(self::$_head['link']) and !empty(self::$_head['link'])) {
			foreach (self::$_head['link'] as $key => $val) {
				$head .= "\t<link";
				if (isset($val['rel'])) {
					$head .= ' rel="' . $val['rel'] . '"';
				}
				
				if (isset($val['href'])) {
					$head .= ' href="' . $val['href'] . '"';
				}
				
				if (isset($val['type'])) {
					$head .= ' type="' . $val['type'] . '"';
				}
				$head .= "/>\n";
			}
		}
		
		// Мета тэги.
		if (isset(self::$_head['meta']) and !empty(self::$_head['meta'])) {
			foreach (self::$_head['meta'] as $type => $meta) {
				foreach ($meta as $name => $content) {
					if (!empty($content)) {
						$head .= "\t<meta $type=\"$name\" content=\"$content\" />\n";
					}
				}
			}
		}
		
		// @todo вынести ScriptsLib из класса, пока можно в Кернел.
		include DIR_SYSTEM . '_temp/html_head_script_lib.php';

		// Стили.
		if (isset(self::$_head['styles']) and !empty(self::$_head['styles'])) {
			foreach (self::$_head['styles'] as $key => $val) {
				$head .= "\t<style";
				if (isset($val['type'])) {
					$head .= ' type="' . $val['type'] . '">';
				} else {
					$head .= ' type="text/css">';
				}
				
				if (isset($val['href'])) {
					$head .= ' @import "' . $val['href'] . '"; ';
				}
				
				if (isset($val['data'])) {
					$head .= $val['data'];
				}
				
				$head .= "</style>\n";
			}
		}
		
		// Скрипты. 		
		if (isset(self::$_head['scripts']) and !empty(self::$_head['scripts'])) {
			foreach (self::$_head['scripts'] as $key => $val) {
				$head .= "\t<script";
				if (isset($val['type'])) {
					$head .= ' type="' . $val['type'] . '"';
				} else {
					$head .= ' type="text/javascript"';
				}
				
				if (isset($val['src'])) {
					$head .= ' src="' . $val['src'] . '"';
				}
				
				$head .= ">";
				if (isset($val['data'])) {
					$head .= $val['data'];
				}
				
				$head .= "</script>\n";
			}
		}

		// Обработчик 'document-ready'.
		if (isset(self::$_head['document-ready']) and !empty(self::$_head['document-ready'])) {
			$head .= "\t<script type=\"text/javascript\">";
			$head .= '	$j = jQuery.noConflict(); $j(function(){' . "\n";
//			$head .= '	$(function(){' . "\n";
			foreach (self::$_head['document-ready'] as $js_code) {
				$head .= $js_code;
				$head .= "\n";
			}
			$head .= "\t});\n\t</script>\n";
		}
		
		// Произвольные данные.
		if (isset(self::$_head['_data']) and !empty(self::$_head['_data'])) {
			foreach (self::$_head['_data'] as $data) {
				$head .= $data;
				$head .= "\n";
			}
		}

		$head .= "</head>\n";
		echo $head;
	}
	
	/**
	 * Отрисовка секции <body>
	 */
	public function body()
	{
		// Установка атрибутов <body>.
		$body_attr = '';
		foreach ($this->body_attributes as $key => $value) {
			$body_attr .= " $key=\"$value\"";
		}

		echo "<body$body_attr>\n";
				
		echo @$this->Toolbar; // @todo переделать!!!!!!!!!!!!!
		
		// @todo включение базового вида
		
		$this->includeTpl();
		
		echo "\n</body>\n</html>";
	}
}