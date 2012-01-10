<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Smart Core CMF
 * 
 * Запуск задач по расписанию. Этот скрипт применяется для запуска через GET запрос.
 * 
 * @category	System
 * @package 	Kernel
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://www.opensource.org/licenses/gpl-2.0
 * 
 * @version 	2011-11-19.1
 */
// До запуска bootstrap.php включительно, код идентичен index.php, далее выполняется проверка на cron_key и запуск метода ядра cron().
define('START_TIME', microtime(true));	// Время старта проекта.
define('DIR_ROOT',   getcwd() . '/');	// Корневая папка платформы.
define('INDEX_PHP_VERSION', 2);			// Версия index.php 

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

// Проверка ключа крона.
if (!isset($_GET['cron_key']) or empty($_GET['cron_key']) or $_GET['cron_key'] !== $config['cron_key']) {
	// Cron could not run because an invalid key was used.
	echo 'Cron could not run because an invalid key was used.';
	exit;
}

// Запуск крона.
Kernel::getInstance($config)->cron();