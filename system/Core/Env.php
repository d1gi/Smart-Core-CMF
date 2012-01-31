<?php
/**
 * Базовый класс, предоставляющий данные окружения.
 * 
 * @author		Artem Ryzhkov
 * @category	System
 * @package		Kernel
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version		2011-11-18.0
 * 
 * @todo возможно есть смысл держать здесь информацию о браузере, например она юзается в логгере
 * 		 также можно сделать через магические методы _get() в таком случае свойства окружения невозможно переписать, что есть некоторая степень безопасности
 * 		 а с другой стороны, мы можем генерировать данные в тот момент, когда они затребованы, например таже инфа о браузере.
 * - ip
 * - user_agent
 * - browser
 * - browser_version
 * - platform
 * - requested_uri
 * - referer
 */
class Env extends Singleton
{
	public $cache_enable;
	public $dir_application;
	public $dir_sites;
	public $dir_theme;
	public $site_id;
	public $language_id;
	public $default_language_id;
	public $current_folder_id;
	public $current_folder_path;
	public $user_id;
	public $user;
		
	/**
	 * Constructor.
	 * 
	 * @param array $env 
	 */
	protected function __construct(array $env = array())
	{
		foreach ($env as $key => $value) {
			$this->$key = $value;
		}

		// @todo запонение развернутой информации о пользователе.
		$this->user_id	= 0;
		$this->user		= array(
			'id'				=> 0,
			'groups'			=> false,
			'email'				=> false,
			'displayed_name'	=> false,
			'timezone'			=> false,
			);
	}
	
	/**
	 * Установка ID пользователя.
	 *
	 * @param int $user_id
	 */
	public function setUserId($user_id)
	{
		$this->user_id		= $user_id;
		$this->user['id']	= $user_id; // @todo временно...
	}
	
	/**
	 * Установить значение окружения.
	 *
	 * @param string $name
	 * @param mixed $val
	 */
	public function setVal($name, $val)
	{
		$this->$name = $val;
	}
}
