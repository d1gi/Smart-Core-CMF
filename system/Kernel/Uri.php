<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Хелпер для обработки частей запроса URI.
 * 
 * @package		Kernel
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version 	2012-01-09.0
 */
class Uri
{
	/**
	 * Проверить является ли ссылка абсолютой.
	 * 
	 * @param string $uri
	 * @return bool
	 */
	static public function isAbsolute($uri)
	{
		return (strpos($uri, '/') === 0 
			or strpos($uri, 'http://') === 0
			or strpos($uri, 'https://') === 0
			or strpos($uri, 'ftp://') === 0
			) ? true : false;		
	}
	
	/**
	 * Разбор фрагмента запроса.
	 *
	 * @param string $uri
	 * @return array
	 */
	static public function parser($uri)
	{
		$tmp = parse_url($uri);
		$uri_path_parts = explode('/', $tmp['path']);
		
		$cnt = 0;
		$data = array();
		$parts_count = count($uri_path_parts);
		
		foreach ($uri_path_parts as $key => $value) {
			if ($key == 0 and empty($value)) {
				$parts_count--;
				continue;
			}
			
			if (empty($value)) {
				$data[$cnt - 1]['is_file'] = false;
				continue;
			}
			
			$data[$cnt++] = array(
				'name' => $value,
				'is_file' => false,
				);

			if ($cnt == $parts_count) {
				$data[$cnt - 1]['is_file'] = true;
			}				
		}
		return $data;
	}
}