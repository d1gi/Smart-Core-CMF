<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Шаблонизатор
 * 
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @uses 		Admin_Toolbar
 * @uses 		Component_Popup
 * @uses 		EE
 * @uses 		Helper_Head
 * @uses 		Session
 * 
 * @version 	2011-12-23.0
 */	
class Template extends Base
{
	private $views;
	private $layout;

	private $dir_layouts;
	private $dir_theme;
	
	private $doctype;
	private $content_language;
	
	private $frontend_mode;
	private $show_toobar;
	
	/**
	 * Конструктор.
	 */
	public function __construct()
	{
//		$this->EE = EE::getInstance();
		
		//$this->EE->head['meta']['name']['generator'] = 'Smart Core CMF v' . Kernel::VERSION . ' build ' . Kernel::VERSION_BUILD . ' (' . Kernel::VERSION_DATE . ') (http://smart-core.org)';

		$theme_tmp = $this->EE->template['dir_theme'] . $this->EE->template['theme_name'];
		
		if ($this->EE->template['dir_theme'][0] == '/' or $this->EE->template['dir_theme'][1] == ':') { // Задан абсолютный путь.
			$this->dir_theme	= $theme_tmp;
			$theme_path			= $theme_tmp;
		} else {        
			$this->dir_theme	= DIR_ROOT  . $theme_tmp;
			$theme_path			= HTTP_ROOT . $theme_tmp;
		}
		
		// Чтение конфига темы.
		if (file_exists($theme_tmp . 'tpl/' . 'theme.ini')) {
			$theme_ini	= parse_ini_file($theme_tmp . 'tpl/' . 'theme.ini');
		}
		
		$doctype	 = isset($theme_ini['doctype']) ? $theme_ini['doctype'] : 'XHTML1_STRICT';
		$css_path	 = isset($theme_ini['css_path']) ? $theme_ini['css_path'] : 'css/';
		$images_path = isset($theme_ini['images_path']) ? $theme_ini['images_path'] : 'images/';
		$js_path	 = isset($theme_ini['js_path']) ? $theme_ini['js_path'] : 'js/';
		
		if (isset($theme_ini['shortcut_icon'])) {
			$ext = strtolower(substr($theme_ini['shortcut_icon'], strrpos($theme_ini['shortcut_icon'], '.') + 1));
			switch ($ext) {
				case 'png':
					$type = 'image/png';
					break;
				case 'gif':
					$type = 'image/gif';
					break;
				default;
					$type = 'image/x-icon';
			}
			$this->EE->addHeadLink('shortcut_icon', array(
				'rel' => 'shortcut icon',
				'href' => $theme_path . $theme_ini['shortcut_icon'],
				'type' => $type,
				));
		}
		
		$this->views			= unserialize($this->EE->template['views']);
		$this->layout			= $this->EE->template['layout'];
		$this->dir_layouts		= $this->dir_theme . 'tpl/layouts/'; // Путь к папке с макетами.
		$this->content_language	= $this->EE->template['content_language'];
		$this->show_toobar		= false;
		$this->frontend_mode	= true;
		$this->doctype			= $doctype;
		
		define('HTTP_THEME',		$theme_path); 
		define('HTTP_THEME_CSS',	$theme_path . $css_path);
		define('HTTP_THEME_IMAGES',	$theme_path . $images_path);
		define('HTTP_THEME_JS',		$theme_path . $js_path);
		
		if ($this->Cookie->cmf_frontend_mode === 'view') { // @todo ненравится мне этот момент...
			$this->frontend_mode = false;
		}
	}
	
	/**
	 * Генерируется тект JS функции для отрисовки фронт-енд контролсов.
	 * 
	 * @todo !!! Вынести в отдельный класс.
	 */
	private function _generateFrontControls()
	{
		$tmp = 'function frontControls() {';
		
		foreach ($this->EE->admin['frontend'] as $key => $value) {
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
	 * Запуск шаблонизатора. 
	 * 
	 * @uses Admin_Toolbar
	 * @uses Component_Popup
	 * @uses EE
	 * 
	 * @return void
	 */
	public function render()
	{
		echo $this->doctype();
		
		$this->EE->addHeadMeta('Content-Type',  'text/html; charset=utf-8', 'http-equiv');
		$this->EE->addHeadMeta('Content-Language', $this->content_language, 'http-equiv');

		// Чтение ini-шника макета.
		if (file_exists($this->dir_layouts . $this->layout . '.ini')) {
			$layout_ini = parse_ini_file($this->dir_layouts . $this->layout . '.ini');
			// Отображать ли фронт-админку.
			$this->frontend_mode = (isset($layout_ini['front_controls']) and $layout_ini['front_controls'] == true and $this->frontend_mode) ? true : false;
		}
				
		// В случае, если есть фронт-енд элементы управления, добавляем ресурсы и генерируем JS скрипт.
		if (isset($this->EE->admin['frontend']) and count($this->EE->admin['frontend']) > 0  and $this->frontend_mode) {
			$this->EE->useScriptLib('jquery');

			$this->EE->addHeadStyle('frontend-ie', array('href' => HTTP_SYS_RESOURCES . 'admin/frontend/node_control-ie.css', 'ie' => 'lt IE 8')); // @todo продумать как подключать стили для ИЕ.
			$this->EE->addHeadStyle('frontend',  HTTP_SYS_RESOURCES . 'admin/frontend/node_control.css');
			$this->EE->addHeadScript('frontend', HTTP_SYS_RESOURCES . 'admin/frontend/front_controls2.js');
			$this->EE->addHeadScript('_generateFrontControls', array('data' => $this->_generateFrontControls()));
			$this->EE->addDocumentReady('frontControls();');
			
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
					$this->EE->addHeadStyle(trim($script_name), HTTP_THEME_CSS . trim($script_name));
				}
			}
			
			// Подключение JS-библиотек.
			if (isset($layout_ini['js_lib'])) {
				$scripts = explode(',', $layout_ini['js_lib']);
				foreach ($scripts as $script_name) {
					$this->EE->useScriptLib(trim($script_name));
				}
			}
		
			// Подключение JS скриптов макета.
			if (isset($layout_ini['js'])) {
				$scripts = explode(',', $layout_ini['js']);
				foreach ($scripts as $script_name) {
					$this->EE->addHeadScript("layout script $script_name", HTTP_THEME_JS . trim($script_name));
				}
			}
			
			// Отображать ли toobar.
			$this->show_toobar = (isset($layout_ini['toolbar']) and $layout_ini['toolbar'] == true) ? true : false;
		}

		// @todo ВАЖНО! сделать адекватное подключение тулбара. сейчас отображается только для групп рута и админа.
		if (($this->Permissions->isAdmin() or $this->Permissions->isRoot()) and $this->show_toobar and $this->layout !== 'blank') {
			$Toolbar = new Admin_Toolbar();
		}
		
		// Подготовка массива EE.
		$this->EE->preparation();
		
		// Отображение секции <head>.
		// @todo возможно лучше сделать метод renderHead() и выводить данные сразу, а не возвращать их, а потом отображать...
		echo $this->getHead();
		
		// Установка атрибутов <body>.
		$body_attr = '';
		foreach ($this->EE->template['body_attributes'] as $key => $value) {
			$body_attr .= " $key=\"$value\"";
		}
		
		// Закрываем секцию <head> и открываем секцию <body>
		echo "<body$body_attr>\n";
		
		// Отображение тулбара, если доступен.
		// @todo убрать отсюда.
		echo @$Toolbar;

		// Подключение макета.
		include_once $this->dir_layouts . $this->layout . '.tpl';
		
		// Закрытие документа.
		echo "\n</body>\n</html>";
	}
	
	/**
	 * Метод отрисовки данных контейреров, вызывается из шаблонов т.е. представлений (views). 
	 * 
	 * Формат массива $options:
	 * - class - имя класса.
	 * - id - id тега.
	 * - before - код предшествующий выводу шаблона в случае, если контейнер не пустой.
	 * - after - код последующий выводу шаблона в случае, если контейнер не пустой.
	 * 
	 * @param string $container
	 * @param string $start_tag
	 * @param string $end_tag
	 * @param array $options
	 * @return bool
	 */
	private function container($container, $options = false)
	{
		if (!isset($this->EE->data[$container])) {
			return false;
		}
		
		$class	= isset($options['class'])	? $options['class']		: false;
		$id		= isset($options['id'])		? $options['id']		: false;
		$before = isset($options['before'])	? $options['before']	: '';
		$after	= isset($options['after'])	? $options['after']		: '';
		
		if (count($this->EE->data[$container]) > 0) {
			echo $before;
		}

		foreach ($this->EE->data[$container] as $node_id => $node_data) {
			
			// Получен готовый HTML фрагмент, в этом случае он просто выводится.
			if (isset($node_data['html_cache'])) {
				echo $node_data['html_cache'];
				continue;
			}
			
			// Получен запрос на сохранение HTML фрагмента в кеше.
			if (isset($node_data['store_html_cache']) and !empty($node_data['store_html_cache'])) {
				ob_start();
				ob_implicit_flush(false);
			}
			
			// Передаём часть данных в шаблоны модулей по ссылке, чтобы не копировать даные.
			// В шаблонах данные модуля буду доступны через переменную $data.
			$data = &$node_data['data'];
			
			// встроенный механизм действия над нодой.
			/*
			if ($mod_data['node_action_mode'] === 'built-in') {
				cf_debug($mod_data);
				//continue;
			}
			*/
			// Если есть фронт-енд элементы управления для этой ноды, то оборачиваем в div с классом "cmf-frontadmin-node".
			if (isset($this->EE->admin['frontend'][$node_id]) and $this->frontend_mode === true) {
				echo "<div class=\"cmf-frontadmin-node\" id=\"_node$node_id\">";
			}
			
			// подразумеваем, что если данных у модуля нет, то он пустой и его шаблон не запускается.
			if (count($data) != 0) {

				if (empty($node_data['tpl'])) {
					break;
				}
				
				$tpl_path = $node_data['tpl_path'] . '/' . $node_data['tpl'] . '.tpl';

				// Задан абсолютный путь к шаблону модуля.
				if (cf_is_absolute_path($node_data['tpl'])) {
					include $node_data['tpl'];
				}
				// Задан абсолютный путь к папке с шаблонами.
				elseif (cf_is_absolute_path($tpl_path)) {
					include $tpl_path;
				}
				// Сначала проверяется на наличие файл шаблона прописанный в теме.
				elseif (file_exists($this->dir_theme . 'tpl/' . $tpl_path)) {
					include $this->dir_theme . 'tpl/' . $tpl_path;
				}
				// Затем существует ли файл в папке приложения.
				elseif (file_exists(Site::getDirApplication() . $tpl_path)) {
					include Site::getDirApplication() . $tpl_path;
				}
				// Затем в системной папке.
				else {
					include DIR_SYSTEM . $tpl_path;
				}
			}
			
			// Если есть фронт-енд элементы управления для этой ноды, то оборачиваем в div с классом "cmf-frontadmin-node".
			if (isset($this->EE->admin['frontend'][$node_id]) and $this->frontend_mode === true) {
				echo '</div>';
			}
			
			if (isset($node_data['store_html_cache']) and !empty($node_data['store_html_cache'])) {
				$html_cache = ob_get_clean();
				$this->Cache_Node->saveHtml($node_data['store_html_cache'], $html_cache);
				echo $html_cache;
			}
			
		} // __end foreach ($this->EE->data[$container] as $node_id => $node_data)
		
		if (count($container) > 0) {
			echo $after;
		}
		
		return true;
	}
	
	/**
	 * Представление (view) контейнеров внутри макетов (layout).
	 * 
	 * @param string $name
	 * @param bool $force
	 * @return bool
	 * 
	 * @todo пересмотреть поведение с $force, а также наличие $this->views вообще.
	 */
	private function view($name, $force = false)
	{
		$tmp = 0;
		if ($force) {
			$tmp = $this->dir_layouts . '../views/' . $view . '.tpl';
		} elseif (isset($this->views[$name])) {
			$view = $this->views[$name];
			$tmp = $this->dir_layouts . '../views/' . $view . '.tpl';
		}

		if (file_exists($tmp)) {
			include $tmp;
			return true;
		}
		
		return false;	 	
	}
	
	/**
	 * Получение содержимого секции <head>.
	 *
	 * @return text
	 */
	private function getHead()
	{
		$head = "\n<head>\n";
		
		if (isset($this->EE->head['title'])) {
			$head .= "\t<title>" . $this->EE->head['title'] . "</title>\n";
		}
		
		// Тэги <link>.
		if (isset($this->EE->head['link']) and !empty($this->EE->head['link'])) {
			foreach ($this->EE->head['link'] as $key => $val) {
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
		if (isset($this->EE->head['meta']) and !empty($this->EE->head['meta'])) {
			foreach ($this->EE->head['meta'] as $type => $meta) {
				foreach ($meta as $name => $content) {
					$head .= "\t<meta $type=\"$name\" content=\"$content\" />\n";
				}
			}
		}
		
		// Стили. style
		if (isset($this->EE->head['style']) and !empty($this->EE->head['style'])) {
			foreach ($this->EE->head['style'] as $key => $val) {
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
		if (isset($this->EE->head['script']) and !empty($this->EE->head['script'])) {
			foreach ($this->EE->head['script'] as $key => $val) {
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
		if (isset($this->EE->head['document-ready']) and !empty($this->EE->head['document-ready'])) {
			$head .= "\t<script type=\"text/javascript\">";
			$head .= '	$j = jQuery.noConflict(); $j(function(){' . "\n";
//			$head .= '	$(function(){' . "\n";
			foreach ($this->EE->head['document-ready'] as $js_code) {
				$head .= $js_code;
				$head .= "\n";
			}
			$head .= "\t});\n\t</script>\n";
		}
		
		// Произвольные данные.
		if (isset($this->EE->head['_data']) and !empty($this->EE->head['_data'])) {
			foreach ($this->EE->head['_data'] as $data) {
				$head .= $data;
				$head .= "\n";
			}
		}

		$head .= "</head>\n";
		return $head;
	}
	
	/**
	 * Получение doctype.
	 * 
	 * @return text $doctype
	 */
	private function doctype()
	{
		$doctype = '';
		switch ($this->doctype) {
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
		
		return $doctype;
	}
	
	/**
	 * Отобразить путь к картикам темы.
	 *
	 * @param string $img
	 */
	public function img($file = '')
	{
		echo HTTP_THEME_IMAGES . $file;
	}

	/**
	 * Отобразить путь к стилям темы.
	 *
	 * @param string $img
	 */
	public function css($file = '')
	{
		echo HTTP_THEME_CSS . $file;
	}
	
	/**
	 * Отобразить путь к JS файлам темы.
	 *
	 * @param string $img
	 */
	public function js($file = '')
	{
		echo HTTP_THEME_JS . $file;
	}
}