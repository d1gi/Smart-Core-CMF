<?php
/**
 * View
 * 
 * @author		Artem Ryzhkov
 * @category	System
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version		2012-01-31.0
 */
class View
{
	/**
	 * Список всех доступных путей, в которых будет производиться поиск файла с шаблоном.
	 */
	static protected $__paths = array();
	
	/**
	 * Опции.
	 * @var array
	 */
	protected $__options = array();
	
	/**
	 * Constructor.
	 *
	 * @param array $options
	 */
	public function __construct(array $options = array())
	{
		$this->__options = array(
			'comment'	=> null,
			'tpl_name'	=> null,
			'tpl_path'	=> null,
			'controller'=> false, // $this
			'action'	=> 'includeTpl', // echoProperties
			'decorators' => null,
			);
		$this->__options = $options + $this->__options;
	}
	
	/**
	 * Получить глобальные пути в которых производится поиск шаблонов.
	 * 
	 * @return array
	 */
	static public function getPaths()
	{
		return self::$__paths;
	}
	
	/**
	 * Установить глобальные пути в которых производится поиск шаблонов.
	 * 
	 * @param array $paths
	 */
	static public function setPaths($paths)
	{
		self::$__paths = $paths;
	}
	
	/**
	 * Добавить глобальный путь в конец списка.
	 * 
	 * @param string $path
	 */
	static public function appendPath($path)
	{
		self::$__paths[] = $path;
	}
		
	/**
	 * Добавить глобальный путь в начало списка.
	 * 
	 * @param string $path
	 */
	static public function prependPath($path)
	{
		array_unshift(self::$__paths, $path);
	}
		
	/**
	 * Отобразить все свойства.
	 */
	public function echoProperties()
	{
		foreach ($this as $property => $__dummy) {
			if ($property == '__options') {
				continue;
			}
			echo $this->$property;
		}
	}

	/**
	 * Получить список свойств
	 * @return array
	 */
	public function getPropertiesList()
	{
		$properties = array();
		foreach ($this as $property => $_dummy) {
			if ($property === '__options') {
				continue;
			}			
			$properties[] = $property;
		}
		return $properties;
	}
	
	/**
	 * Получить имя шаблона, по умолчанию при инициализации модуля устанавливается такое же как
	 * имя класса, но в результате работы, модуль может установить другой шаблон.
	 * 
	 * @access public
	 * @final
	 * @return string
	 */
	final public function getTpl()
	{
		return $this->__options['tpl_name'];
	}	
	
	/**
	 * NewFunction
	 */
	public function setActionMethod($method)
	{
		$this->__options['action'] = $method;
	}
	
	/**
	 * NewFunction
	 */
	public function setDecorators($before, $after)
	{
		$this->__options['decorators'] = array($before, $after);
	}
	
	/**
	 * NewFunction
	 */
	public function setTpl($name)
	{
		$this->__options['tpl_name'] = $name;
	}
	
	/**
	 * Установить путь к шаблону объекта.
	 *
	 * @param string $val
	 */
	final public function setTplPath($val)
	{
		$this->__options['tpl_path'] = $val;
	}

	/**
	 * Установка свойства.
	 */
	public function set($name, $value)
	{
		$this->$name = $value;
	}
	
	/**
	 * Установка свойства.
	 */
	public function __set($name, $value)
	{
		$this->$name = $value;
	}
	
	/**
	 * Получить свойство.
	 */
	public function getProperty($name)
	{
		return isset($this->$name) ? $this->$name : null;
	}
	
	/**
	 * NewFunction
	 *
	 * @param
	 * @return
	 */
	public function __get($name)
	{
		return isset($this->$name) ? $this->$name : null;
	}
	
	/**
	 * Проверить существует ли свойство.
	 *
	 * @param $name
	 * @return bool
	 */
	public function isExist($name)
	{
		return isset($this->$name) ? true : false;
	}
	
	/**
	 * Отрисовка формы.
	 * @return text
	 */
	public function __tostring()
	{
		ob_start();
		$this->render();
		return ob_get_clean();
	}

	/**
	 * Базовый метод отрисовки шаблона с помощью включения файла шаблона.
	 */
	public function includeTpl()
	{
		if (empty($this->__options['tpl_path'])) {
			$tpl = $this->__options['tpl_name'] . '.tpl';
		} else {
			$tpl = $this->__options['tpl_path'] . '/' . $this->__options['tpl_name'] . '.tpl';
		}
		
		// Задан абсолютный путь к файлу шаблона.
		if (cmf_is_absolute_path($this->__options['tpl_name'])) {
			include $this->__options['tpl_name'];
		} else {
			foreach (self::$__paths as $path) {
				if (file_exists($path . $tpl)) {
					include $path . $tpl;
					break;
				}
			}
		}
	}
	
	/**
	 * NewFunction
	 */
	public function render()
	{		
		if (!empty($this->__options['decorators'])) {
			echo $this->__options['decorators'][0];
		}
		$is_recusive = false;
		$props = $this->getPropertiesList();
		
		foreach ($props as $key => $value) {
			if ($this->$value instanceof View) {
				$is_recusive = true;
				continue;
			}
			$is_recusive = false;
		}
		
		if ($is_recusive) {
			foreach ($props as $key => $value) {
				$this->$value->render();				
			}
			return;
		}
		
		if (count($props) == 0) {
			return;
		}
		
		$action = $this->__options['action'];
		$this->$action();
		if (!empty($this->__options['decorators'])) {
			echo $this->__options['decorators'][1];
		}
	}
}