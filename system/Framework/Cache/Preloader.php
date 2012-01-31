<?php
/**
 * Предзагрузчик кеша.
 * 
 * @author		Artem Ryzhkov
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version 	2012-01-04.0
 */
class Cache_Preloader
{
	/**
	 * Папка где хранятся файлы кеша.
	 * @var string
	 */
	static protected $_pages_subdir_static = 'pages/';
		 
	/**
	 * Предзагрузка кеша.
	 * 
	 * Пробует найти кеш запрошенного файла и отдаёт его, если он есть.
	 */
	public static function run()
	{
		// В случае наличия POST данных, кеш не обрабатывается.
		if (count($_POST) > 0) {
			return null;
		}

		// Формируется полный путь до файла кеша, без раслирения
		$file = DIR_CACHE . self::$_pages_subdir_static . md5(trim($_SERVER['REQUEST_URI']));
		
		$meta = @file_get_contents($file . '.meta');
		
		// Чтение мета данных.
		if ($meta === false) {
			return null;
		}
		
		$meta = unserialize($meta);
		
		// Устаревший кеш.
		if ($meta['valid_to_timestamp'] < time()) {
			return null;
		}

		// Проверка на допустимые куки.
		foreach ($_COOKIE as $key => $value) {
			if (isset($meta['rules']['cookies'][$key]) and $meta['rules']['cookies'][$key] == 0) {
				return null;
			}
		}
		
		// Отдача кеша.
		$compressed_content = @file_get_contents($file . '.gz');
		
		if ($compressed_content === false) {
			return null;
		}
		
		$is_support_compress = (isset($_SERVER['HTTP_ACCEPT_ENCODING']) and strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false and strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip;q=0') === false and extension_loaded('zlib')) ? true : false;

		if ($is_support_compress) {
			foreach ($meta['headers'] as $value) {
				header($value);
			}
			echo $compressed_content;
		} else {
			foreach ($meta['headers'] as $value) {
				if ($value == 'Content-Encoding: gzip' or strpos($value, 'Content-Length:') === 0 or strpos($value, 'X-Uncompressed-Length:') === 0) {
					continue;
				}
				header($value);
			}
			echo gzinflate(substr($compressed_content, 10, -8));
		}
		
		if ($meta['is_stat_enable']) {
			// Инкремент счетчика кликов.
			if (file_exists($file . '.stat')) {
				$cnt = (int) file_get_contents($file . '.stat');
				file_put_contents($file . '.stat', $cnt + 1);
				header('X-Cache-Counter: ' . $cnt);
			}
		}
		
		// В случае удачной отдачи контента работа скрипта прекращается.
		exit;
	}
}