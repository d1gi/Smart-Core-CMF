<?php
/**
 * Базовый контроллер.
 * 
 * @author		Artem Ryzhkov
 * @category	System
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @uses		View
 * 
 * @version		2012-01-31.0
 */
abstract class Controller extends Container
{
	/**
	 * View object
	 * @var View
	 */
	public $View;
	
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
	 * Version.
	 * @var string
	 */
	private $_object_version = 0.0;

	/**
	 * Constructor.
	 * 
	 * Вызывается как parent::__construct(); из дочерних классов.
	 */
	public function __construct()
	{
		$this->View	= new View();
	}
	
	/**
	 * Роутинг.
	 *
	 * @param string $path
	 * @return array|null
	 */
	public function router($path)
	{
		return null;
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
	 * Установить версию объекта.
	 * 
	 * @access protected
	 * @param float $ver
	 */
	final protected function setVersion($ver)
	{
		$this->_object_version = $ver;
	}
	
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
	final protected function setErrorMessage($val)
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
}