<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Базовый класс объектов.
 * 
 * @author		Artem Ryzhkov
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version 	2011-12-08.0
 */
class Object
{
	/**
	 * Template.
	 * @var string
	 */
	private $_object_template = '';
	
	/**
	 * Template path.
	 * @var string
	 */
	private $_object_template_path = '';
	
	/**
	 * Version.
	 * @var string
	 */
	private $_object_version = 0.0;
	
	/**
	 * Код ошибки.
	 * @var int
	 */
	private $_error_code = 0;
	
	/**
	 * Текс сообщения об ошибки.
	 * @var string
	 */
	private $_error_message = '';
	
	/**
	 * Массив с выходными данными объекта.
	 * 
	 * @access protected
	 * @var array
	 */
	protected $output_data = array();
	
	/**
	 * Установка кода ошибки.
	 * 
	 * @param int $val
	 */
	final protected function setErrorCode($val)
	{
		$this->_error_code = $val;
	}
	
	/**
	 * Получение кода ошибки.
	 * 
	 * @return int
	 */
	final public function getErrorCode()
	{
		return $this->_error_code;
	}
	
	/**
	 * Установка сообщения об ошибке.
	 * 
	 * @param string $val
	 */
	final protected function setErrorMessge($val)
	{
		$this->_error_message = $val;
	}
	
	/**
	 * Получение кода сообщения об ошибке.
	 * 
	 * @return string
	 */
	final public function getErrorMessage()
	{
		return $this->_error_message;
	}
	
	/**
	 * Установить имя шаблона. (расширения файлов не указываются т.к. могут использоваться разные шаблонные движки).
	 * 
	 * @access protected
	 * @param string $tpl
	 * @return string
	 */
	final protected function setTpl($tpl)
	{
		$this->_object_template = $tpl;
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
		return $this->_object_template;
	}	
	
	/**
	 * Установить путь к шаблону объекта.
	 *
	 * @param string $val
	 */
	final protected function setTplPath($val)
	{
		$this->_object_template_path = $val;
	}
	
	/**
	 * Получить путь к шаблону объекта.
	 *
	 * @param
	 * @return string
	 */
	final public function getTplPath()
	{
		return $this->_object_template_path;
	}
	
	/**
	 * Установить версию объекта.
	 * 
	 * @access protected
	 * @param float $ver
	 * @return void
	 */
	final protected function setVersion($ver)
	{
		$this->_object_version = $ver;
	}
	
	/**
	 * Получить версию объекта.
	 * 
	 * @access public
	 * @return float
	 */
	final public function getVersion()
	{
		return $this->_object_version;
	}
	
	/**
	 * Получить выходные данные работы объекта.
	 * 
	 * @access public
	 * @return array
	 */
	final public function getOutputData()
	{
		// @todo разобраться можно ли так возвращать не весь массив с данными, а только ссылку на него?
		//$o = &$this->output_data;
		//return $o;
		
		return $this->output_data;
	}
}