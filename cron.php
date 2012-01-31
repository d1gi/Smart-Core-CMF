<?php
/**
 * Smart Core CMF (Content Managment Framework/System)
 * 
 * Запуск задач по расписанию. Этот скрипт применяется для запуска через GET запрос.
 * 
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://www.opensource.org/licenses/gpl-2.0
 * 
 * @version 	2012-01-25.0
 */
// До запуска bootstrap.php включительно, код идентичен index.php, далее выполняется проверка на cron_key и запуск метода ядра cron().
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

// Проверка ключа крона.
if (!isset($_GET['cron_key']) or empty($_GET['cron_key']) or $_GET['cron_key'] !== $config['cron_key']) {
	// Cron could not run because an invalid key was used.
	echo 'Cron could not run because an invalid key was used.';
	exit;
}

// Запуск крона.
$App = new Kernel($config);
$App->cron();