<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Smart Core CMF (Content Managment Framework/System)
 * 
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://www.opensource.org/licenses/gpl-2.0
 * 
 * @version 	2012-01-25.0
 */
define('START_TIME', microtime(true));	// Время старта проекта.
define('DIR_ROOT',   getcwd() . '/');	// Корневая папка платформы.
define('INDEX_PHP_VERSION', 4);			// Версия index.php 

// Читается файл конфига.
$cfg_ini = parse_ini_file('config.ini', true);

// Попытка прочитать конфиг который совпадает с девелоперским IP.
if (isset($cfg_ini['developer_addresses']) and array_search($_SERVER['SERVER_ADDR'], explode(';', $cfg_ini['developer_addresses'])) !== false and file_exists('config_' . $_SERVER['SERVER_ADDR'] . '.ini')) {
	$cfg_ini = parse_ini_file('config_' . $_SERVER['SERVER_ADDR'] . '.ini', true) + $cfg_ini;
}

// Формируется константа DIR_SYSTEM - системная папка.
if (isset($cfg_ini['dir_system']) and file_exists($cfg_ini['dir_system'] . 'bootstrap.php')) {
	define('DIR_SYSTEM', $cfg_ini['dir_system']);
} else {
	define('DIR_SYSTEM', DIR_ROOT . 'system/');
}

// Начальный загрузчик.
require_once DIR_SYSTEM . 'bootstrap.php';

// Предзагрузчик кеша.
Cache_Preloader::run();

// Запуск ядра.
$App = new Kernel($config);
$App->run();