<?php
/**
 * Профайлер.
 * 
 * @author		Artem Ryzhkov
 * @category	System
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @uses		DB
 * 
 * @version		2012-01-17.0
 */
class Profiler 
{
	private static $profiler = array();
	
	/**
	 * NewFunction
	 */
	static public function start($tag, $name)
	{
		if (count(self::$profiler) == 0) {
			self::$profiler['preloading'] = array(
				'memory' => memory_get_usage(),
				'time' => microtime(true) - START_TIME,
				);
		}
		
		// Засекание времени
		self::$profiler[$tag][$name]['start_memory'] = memory_get_usage();
		self::$profiler[$tag][$name]['start_time'] = microtime(true);
		self::$profiler[$tag][$name]['start_db_count'] = DB::getQueryCount();
		
		if (DEBUG_DB_QUERY and $name !== 'all_nodes') {
			self::$profiler[$tag][$name]['start_db_query_log'] = count(DB::$query_log);
		}
	}
	
	/**
	 * Окончание измерения.
	 * 
	 * @todo сделать вывод предупреждения, если вызван метод стоп, но предварительно небыл вызван метод старт.
	 */
	static public function stop($tag, $name)
	{
		self::$profiler[$tag][$name]['memory'] = memory_get_usage() - self::$profiler[$tag][$name]['start_memory'];
		self::$profiler[$tag][$name]['time'] = microtime(true) - self::$profiler[$tag][$name]['start_time'];
		self::$profiler[$tag][$name]['db_query_count'] = DB::getQueryCount() - self::$profiler[$tag][$name]['start_db_count'];
		if (DEBUG_DB_QUERY and $name !== 'all_nodes') {
			$cnt = 1;
			$num = self::$profiler[$tag][$name]['start_db_count'];
			while ($num < count(DB::$query_log)) {
				self::$profiler[$tag][$name]['db_query_log'][$cnt++] = DB::$query_log[$num++];
			}
		}
		unset(
			self::$profiler[$tag][$name]['start_time'], 
			self::$profiler[$tag][$name]['start_db_count'], 
			self::$profiler[$tag][$name]['start_db_query_log'], 
			self::$profiler[$tag][$name]['start_memory']
		);
	}
	
	/**
	 * Получить результат.
	 * 
	 * @return array
	 */
	static public function getResult()
	{
		return self::$profiler;
	}
}