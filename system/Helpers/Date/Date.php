<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Работа с датами.
 * 
 * @category	System
 * @package 	Helper
 * 
 * @version 	2011-09-24.0
 */
class Helper_Date
{
	/**
	 * Constcructor.
	 */
	public function __construct()
	{
		/*
		// Получение объекта Zend_Cache_Core
		$cache = Zend_Cache::factory('Core', 'File', array( // $frontendOptions
			   'lifetime' => 7200, // время жизни кэша - 2 часа
			   'automatic_serialization' => true,
			   'automatic_cleaning_factor' => 100,
			), array( // $backendOptions
				'cache_dir' => DIR_CACHE, // директория, в которой размещаются файлы кэша
				//'hashed_directory_level' => 2,
			));
		Zend_Locale_Data::setCache($cache); // @todo запаковать в хелпер Data, чтобы он не стартовал каждый раз, без надобности.
		*/
	}

	/**
	 * Получение даты в формате UNIX timestamp.
	 *
	 * @param string $datetime
	 * @return int|false
	 */
	public function getTimestamp($datetime)
	{
		$result = false;
		$datetime = sscanf($datetime, '%d-%d-%d %d:%d:%d');
		if (count($datetime) == 6) {
			list($year, $month, $day, $hour, $min, $sec) = $datetime;
			$result = mktime($hour, $min, $sec, $month, $day, $year);
		}
		return $result;
	}
	
	/**
	 * Получение даты в формате Datetime (YYYY-MM-DD HH:MM:SS).
	 * 
	 * @param int $timestamp
	 * @return datetime string
	 */
	function getDatetime($timestamp = false)
	{
		if ($timestamp === false) {
			$timestamp = time();
		}
		return date('Y-m-d H:i:s', $timestamp);
	}
	
	/**
	 * NewFunction
	 *
	 * @param
	 * @return
	 */
	public function getYear()
	{
	
		return true;
	}
	
	/**
	 * NewFunction
	 *
	 * @param
	 * @return
	 */
	public function getMonth()
	{
	
		return true;
	}
	
	/**
	 * NewFunction
	 *
	 * @param
	 * @return
	 */
	public function getDay()
	{
	
		return true;
	}
	
	
}
