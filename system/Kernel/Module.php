<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Абстрактный класс для всех модулей.
 * 
 * @author		Artem Ryzhkov
 * @category	System
 * @package		Kernel
 * @subpackage	Module
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version		2011-12-27.0
 * 
 * @uses DB
 * @uses DB_Resources
 * @uses NodeProperties
 */
abstract class Module extends Base implements ModuleInterface
{
	// @todo пересмотреть.
	private $is_writable = false; // право вносить измнения, а также в принципе получать какие либо запросы на действия.

	/**
	 * Прямой вывод данных (в HTTP поток).
	 * @access protected
	 * @var bool
	 */
	protected $direct_output = null; // @todo продумать.
	
	/**
	 * Действие по умолчанию.
	 * @access protected
	 * @var string|false
	 */
	protected $default_action = false;
	
	/**
	 * Фронтальные элементы управления для всего модуля.
	 * @access protected
	 * @var array|false
	 */
	protected $frontend_controls = false;
	
	/**
	 * Фронтальные элементы управления для внутренних элементов модуля.
	 * @access protected
	 * @var array|false
	 */
	protected $frontend_inner_controls = false;
	
	/**
	 * Свойства ноды.
	 * @var object
	 */
	protected $Node;
	
	/**
	 * Базовый конструктор. Модули используют в качестве конструктора метод init();
	 * 
	 * @access public
	 * @param int $node_id
	 * @return void
	 */
	final public function __construct($node_id)
	{
		parent::__construct();
		
		if ($node_id === false) {
			// @todo сообщение о недопустимой операции.
			return null;
		}
		
		$this->Node = new NodeProperties($node_id);

		// По умолчанию устанавливается имя шаблона, как у имени класса, но без префикса 'Module_' и без постфикса '_Admin'.
		$this->setTpl(str_replace(array('Module_', '_Admin'), '', get_class($this)));
		$this->setTplPath('Modules/' . $this->getTpl());
		
		// При database_id = 0 модуль будет использовать тоже подключение, что и ядро, иначе создаётся новое подключение.
		if ($this->Node->database_id != 0) {
			// @todo для совместимости с эмуляцией функции get_called_class для РНР 5.2, дальше для PHP 5.3 only можно будет записывать в одну строку, без $con_data.
			$con_data = DB_Resources::getInstance()->getConnectionData($this->Node->database_id);
			$this->DB = DB::connect($con_data);
			unset($con_data);
		}
		
		// Запуск метода init(), который является заменой конструктора для модулей.
		if (method_exists($this, 'init')) {
			$this->init();
		}
	}

	/**
	 * Деструктор.
	 */
	public function __destruct()
	{
		// @todo есть ли смысл тут удалять объект DB?
		unset($this->DB);
	}
	
	/**
	 * Ajax.
	 *
	 * @param string $uri_path - часть URI, адресованная модулю.
	 * @return ?
	 */
	public function ajax($uri_path)
	{
		return null;
	}
	
	/**
	 * Парсер части УРИ.
	 * 
	 * @access public
	 * @param string $path
	 * @return array|false
	 */
	public function parser($path)
	{
		return null;
	}
	
	/**
	 * @todo скорее всего убрать
	 */
	public function setWritable($val)
	{
		$this->is_writable = $val;
	}
	
	/**
	 * @todo скорее всего убрать
	 */
	public function getWritable()
	{
		return $this->is_writable;
	}
	

	// Ниже описаны ддминистративные методы, они должны быть описаны в классе Module_*_Admin.

	/**
	 * Метод управления модулем.
	 *
	 * @param string $uri_path
	 * @return array
	 */
	public function admin($uri_path)
	{
		return null;
	}
	
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @access public
	 * @return array $params
	 */
	public function getParams()
	{
		return array();
	}
	
	/**
	 * Получить параметры кеширования модуля.
	 * 
	 * @access public
	 * @return array $params
	 */
	public function getCacheParams($cache_params = array())
	{
		$params = array();
		foreach ($cache_params as $key => $value) {
			$params[$key] = $value;
		}
		return $params;
	}
	
	/**
	 * Вызывается при создании ноды.
	 * 
	 * @access public
	 * @return array $params
	 */
	public function createNode()
	{
		$params = $this->Node->getDefaultParams();
		return empty($params) ? 'NULL' : $params;
	}
	
	/**
	 * Метод-заглушка, для модулей, которые не имеют фронт администрирования. 
	 * Возвращает пустой массив или null или 0, следовательно движок ничего не отображает.
	 * 
	 * @access public
	 * @returns array|false
	 * 
	 * @todo определиться какое значение лучше возвращать 0 или false.
	 */
	public function getFrontControls()
	{
		return $this->frontend_controls;
	}
	
	/**
	 * Внутренние элменты управления ноды.
	 * 
	 * @access public
	 * @returns array|false
	 */
	public function getFrontControlsInner()
	{
		return $this->frontend_inner_controls;
	}
	
	/**
	 * Действие по умолчанию.
	 * 
	 * @access public
	 * @returns string|false
	 */
	public function getFrontControlsDefaultAction()
	{
		return $this->default_action;
	}
	
	/**
	 * Выполнение задач по расписанию.
	 *
	 * @access public
	 * @return bool|null
	 */
	public function cron()
	{
		return null;
	}
}