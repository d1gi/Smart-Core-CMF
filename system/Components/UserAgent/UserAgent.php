<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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
	 * @param
	 * @return
	 */
	public function getBrowser()
	{
	
		return true;
	}
	
	/**
	 * Получить платфому (операционную систему) на который работает пользователь
	 *
	 * @param
	 * @return
	 */
	public function getPlatform()
	{
	
		return true;
	}
	
	/**
	 * Получить версию браузера
	 *
	 * @param
	 * @return
	 */
	public function getVersion()
	{
	
		return true;
	}
	
	/**
	 * NewFunction
	 *
	 * @param
	 * @return
	 */
	public function isMobile()
	{
	
		return true;
	}

}
