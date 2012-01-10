<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс Session.
 *  
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version		2011-12-27.0
 */
class Session extends Singleton
{
	/**
	 * Флаг состояния.
	 * @var bool
	 */
	private $is_started = false;
	
	/**
	 * Обработчик сессий.
	 * @var object
	 */
	private $SessionHandler;
	
	/**
	 * Конструктор. Синглтон паттерн.
	 */
	protected function __construct($force_start = false)
	{
		if ($force_start) {
			$this->start();
		}
	}
	
	/**
	 * Открытие сессии.
	 */
	public function start()
	{
		if (!$this->is_started) {
			$this->SessionHandler = new Session_Handler(array(
				'maxlifetime' => 1800,
				'db_table' => 'sessions',
				'gc_probability' => 1,
				));
			
			session_set_save_handler(
				array(&$this->SessionHandler, 'open'),
				array(&$this->SessionHandler, 'close'),
				array(&$this->SessionHandler, 'read'),
				array(&$this->SessionHandler, 'write'),
				array(&$this->SessionHandler, 'destroy'),
				array(&$this->SessionHandler, 'gc')
			) or die('Failed to register session handler.');

			session_start();
			$this->is_started = true;
		}
	}
	
	/**
	 * Установить значение в сессию.
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
		$_SESSION[$name] = $value;
	}
	
	/**
	 * Прочитать значение из сессии.
	 * 
	 * @param string $name
	 * @return string|false
	 */
	public function __get($name)
	{
		/*
		if (!$this->is_started) {
			$this->start();
		}
		*/
		
		return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
	}
	
	/**
	 * Проверка существует ли ключ
	 *
	 * @param string $name
	 * @return bool
	 */
	public function isKeyExist($name)
	{
		return isset($_SESSION[$name]) ? true : false;
	}
	
	/**
	 * Удаление ключа сессии.
	 * 
	 * @param string $name
	 * @return void
	 */
	public function deleteKey($name)
	{
		if (!$this->is_started) {
			$this->start();
		}
		unset($_SESSION[$name]);
	}
	
	/**
	 * Закрытие сессии.
	 * 
	 * @return void
	 */
	public function destroy()
	{
		if ($this->is_started) {
			session_destroy();
			$this->is_started = false;
		}
	}
	
	/**
	 * Получить токен.
	 *
	 * @return string
	 */
	public function getToken()
	{
		if (!$this->is_started) {
			$this->start();
		}
		return $this->SessionHandler->getToken();
	}
	
	/**
	 * Установить токен.
	 *
	 * @param string $token
	 * @return void
	 */
	public function setToken($token)
	{
		if (!$this->is_started) {
			$this->start();
		}
		$this->SessionHandler->setToken($token);
	}
	
	/**
	 * Получение списка групп пользователя.
	 *
	 * @return array
	 */
	public function getUserGroups()
	{
		if (!$this->is_started) {
			$this->start();
		}
		return $this->SessionHandler->getUserGroups();
	}
	
	/**
	 * Получить данные пользователя.
	 *
	 * @return array
	 */
	public function getUserData()
	{
		if (!$this->is_started) {
			$this->start();
		}
		return $this->SessionHandler->getUserData();
	}
	
	/**
	 * Установить данные пользователя.
	 *
	 * @param array $data
	 * @return void
	 */
	public function setUserData(array $data = null)
	{
		if (!$this->is_started) {
			$this->start();
		}
		$this->SessionHandler->setUserData($data);
	}
}