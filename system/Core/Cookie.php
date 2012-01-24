<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс Cookie.
 *  
 * @author		Artem Ryzhkov
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version 	2011-12-27.0
 */
class Cookie extends Singleton
{
	private $prefix;
	private $expire;
	private $path;
	private $domain;
	private $secure;
	private $http_only;
	
	/**
	 * Массив с текущими данными. Применяется для использования параметров до перезагрузки страницы.
	 * @var array
	 */
	private $data;
	
	/**
	 * Конструктор. Синглтон паттерн.
	 */
	public function __construct()
	{
		$this->prefix = ''; // Set a prefix if you need to avoid collisions
		$this->expire = 7776000; // 90 дней
		$this->path = '/'; // Typically will be a forward slash
		$this->domain = ''; // Set to .your-domain.com for site-wide cookies @todo разобраться почему не работает
		$this->secure = false;
		$this->http_only = false;
		$this->data = array();
	}
	
	/**
	 * Инициализация пользовательской конфигурации.
	 * 
	 * @param array $cfg
	 */
	public function init(array $cfg = array())
	{
		foreach ($cfg as $key => $value) {
			$this->$key = $value;
		}
	}
	
	/**
	 * NewFunction
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}
	
	/**
	 * NewFunction
	 *
	 * @return array
	 */
	public function getExpire()
	{
		return $this->expire;
	}
	
	/**
	 * Установить значение в куки.
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function set($name, $value)
	{
		setcookie($this->prefix . $name, $value, time() + $this->expire, $this->path, $this->domain, $this->secure, $this->http_only);
		$this->data[$name] = $value;
	}
	
	/**
	 * Магический метод установки значения в куки.
	 * 
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value)
	{
		$this->set($name, $value);
	}
	
	/**
	 * Прочитать значение из куки.
	 * 
	 * @param string $name
	 * @return null|string
	 */
	public function get($name)
	{
		if (isset($_COOKIE[$this->prefix . $name])) {
			return $_COOKIE[$this->prefix . $name];
		} elseif (isset($this->data[$name])) {
			return $this->data[$name];
		} else {
			return null;
		}
	}

	/**
	 * Магический метод получения значения из куки.
	 * 
	 * @param string $name
	 * @return null|string
	 */
	public function __get($name)
	{
		return $this->get($name);
	}
	
	/**
	 * Удалить значение куки.
	 * 
	 * @param string $name
	 * @return void
	 */
	public function delete($name)
	{
		setcookie($this->prefix . $name, '', 0, $this->path, $this->domain, $this->secure, $this->http_only);
	}
	
	/**
	 * Удалить имя сессии.
	 */
	public function deleteSessionName()
	{
		setcookie(session_name(), '', 0, '/', $this->domain, $this->secure, $this->http_only);
	}
	
	/**
	 * Проверка существует ли в куки ключ сессии.
	 *
	 * @return bool
	 */
	public function isSessionNameExist()
	{
		return isset($_COOKIE[session_name()]) ? true : false;
	}
	
	/**
	 * Получить системный префикс куки.
	 *
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}
}