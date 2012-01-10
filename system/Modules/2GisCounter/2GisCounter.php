<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Module счетчика переходов с 2Гис-а.
 * 
 * @uses Browser 
 * 
 * @package Module
 * @version 2011-07-08.0
 */
class Module_2GisCounter extends Module
{
	/**
	 * Запуск модуля.
	 * 
	 * @return void
	 * 
	 * @todo возможно есть смысл сделать обработчик реферера, типа http://link.2gis.ru/42E294C5/grym/20110801/novosibirsk/807d0000ac4c?http://www.noogen.ru
	 */
	public function run($parser_data)
	{
		$city = 'NULL';
		if (isset($_GET)) {
			foreach ($_GET as $key => $value) {
				$city = $this->DB->quote($key);
				break;
			}
		}
		
		$user_id	= $this->Env->user_id;

		$Browser	= new Browser();
		$browser	= $this->DB->quote($Browser->getBrowser());
		$browser_version = $this->DB->quote($Browser->getVersion());
		$platform	= $this->DB->quote($Browser->getPlatform());
		
		$Date 		= new Helper_Date();
		$datetime	= $Date->getDatetime();

		$ip			= $this->DB->quote($_SERVER['REMOTE_ADDR']);
		$user_agent = $this->DB->quote($_SERVER['HTTP_USER_AGENT']);

		$sql = "
			INSERT INTO {$this->DB->prefix()}2gis_counter
				(user_id, site_id, datetime, city, ip, user_agent, browser, browser_version, platform)
			VALUES
				('$user_id', '{$this->Env->site_id}', '$datetime', $city, $ip, $user_agent, $browser, $browser_version, $platform) ";
		$this->DB->query($sql);
		cf_redirect(HTTP_ROOT);
	}	
}