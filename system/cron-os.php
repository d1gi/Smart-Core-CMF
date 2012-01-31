<?php 
/**
 * Smart Core CMF.
 * 
 * Запуск задач по расписанию. Этот скрипт применяется для запуска системным кроном ОС.
 * 
 * @category	System
 * @package 	Kernel
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://www.opensource.org/licenses/gpl-2.0
 * 
 * @version 	2012-01-31.0
 */
// Для запуска, необходимо корректно указать абсолютный путь к корню платформы в формате '/www/path/to/platform/'.
$dir_platform_root = '/www/path/to/platform/';

define('START_TIME', microtime(true));		// Время старта проекта.
define('DIR_ROOT',   $dir_platform_root);	// Корневая папка платформы.
define('INDEX_PHP_VERSION', 4);				// Версия index.php 

// Читается файл конфига.
$cfg_ini = parse_ini_file($dir_platform_root . 'config.ini', true);

// Формируется константа DIR_SYSTEM - системная папка.
if (isset($cfg_ini['dir_system']) and file_exists($cfg_ini['dir_system'] . 'bootstrap.php')) {
	define('DIR_SYSTEM', $cfg_ini['dir_system']);
} else {
	define('DIR_SYSTEM', DIR_ROOT . 'system/');
}

// Начальный загрузчик.
require_once DIR_SYSTEM . 'bootstrap.php';

// Запуск крона.
$App = new Kernel($config);
$App->cron();