<?php
/**
 * Класс для определения браузера пользователя.
 * 
 * @version 2011-09-24.0
 */
class Component_UserAgent
{
	/**
	 * Имя браузера
	 */
	public $name;
	
	/**
	 * Платформа
	 */
	public $platform;
	
	/**
	 * Получить имя браузера
	 *
	 * @return
	 */
	public function getBrowser()
	{
	
		return true;
	}
	
	/**
	 * Получить платфому (операционную систему) на который работает пользователь
	 *
	 * @return
	 */
	public function getPlatform()
	{
	
		return true;
	}
	
	/**
	 * Получить версию браузера
	 *
	 * @return
	 */
	public function getVersion()
	{
	
		return true;
	}
	
	/**
	 * NewFunction
	 *
	 * @return
	 */
	public function isMobile()
	{
	
		return true;
	}
}