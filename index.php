<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Smart Core CMF (Content Managment Framework/System)
 * 
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://smart-core.org/license/
 * 
 * @version 	2011-11-19.0
 */
define('START_TIME', microtime(true));	// Время старта проекта.
define('DIR_ROOT',   getcwd() . '/');	// Корневая папка платформы.
define('INDEX_PHP_VERSION', 3);			// Версия index.php 

// Читается файл конфига.
$cfg_ini = parse_ini_file('config.ini', true);

// Сначала пытается прочитать конфиг который совпадает с девелоперским IP.
if (isset($cfg_ini['developer_addresses']) and array_search($_SERVER['SERVER_ADDR'], explode(';', $cfg_ini['developer_addresses'])) !== false and file_exists('config_' . $_SERVER['SERVER_ADDR'] . '.ini')) {
	foreach (parse_ini_file('config_' . $_SERVER['SERVER_ADDR'] . '.ini', true) as $key => $value) $cfg_ini[$key] = $value;
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
Kernel::getInstance($config)->run();