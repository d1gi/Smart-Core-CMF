<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Форсированная сессия.
 * 
 * Работает по следующему принципу:
 *  - При установке значения, поднимается сессия и записывается заданное значение в неймспейс класса.
 *  - Также создаётся куки cmf_session_force_start со значением true, что позволит ядру при следующем 
 *    запуске поднять сессию независимо авторизированный юзер или гость.
 *  - Ядро после отработки данных модулей, если обнаруживает в куки ключ cmf_session_force_start, то 
 *    очищается временная сессия и куки.
 *  - Таким образом данные сохранённые через форсированную сессию доступны только при первом редиректе.
 *  
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version		2012-01-10.0
 */
class Session_Force extends Singleton
{
	/**
	 * Флаг состояния.
	 * @var bool
	 */
	protected $is_started = false;
	
	protected $namespace = 'cmf_force';
	
	protected $Session;
	protected $Cookie;

	/**
	 * Открытие сессии.
	 */
	public function start()
	{
		$this->Cookie = Cookie::getInstance();
		$this->Cookie->cmf_session_force_start = true;
		$this->Session = Session::getInstance(true);
		$this->is_started = true;
	}

	/**
	 * Записать значение в сессию.
	 * 
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		if (!$this->is_started) {
			$this->start();
		}
		$_SESSION[$this->namespace][$name] = serialize($value);
	}
	
	/**
	 * Прочитать значение из сессии.
	 * 
	 * @param string $name
	 * @return string|false
	 */
	public function __get($name)
	{
		return isset($_SESSION[$this->namespace][$name]) ? unserialize($_SESSION[$this->namespace][$name]) : null;
	}
	
	/**
	 * Очистка значений временной сессии.
	 *
	 * @return void
	 */
	public function clean()
	{
		if (!$this->is_started) {
			$this->start();
		}
		unset($_SESSION[$this->namespace]);
	}
	
	/**
	 * Проверка существует ли ключ.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function isKeyExist($name)
	{
		return isset($_SESSION[$this->namespace][$name]) ? true : false;
	}
}