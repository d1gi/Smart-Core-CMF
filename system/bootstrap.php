<?php 
/**
 * Smart Core CMF.
 * 
 * Начальная загрузка системы.
 * 
 * @category	System
 * @package 	Kernel
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version 	2012-01-31.0
 */
// Проверка версии index.php
if (!defined('INDEX_PHP_VERSION') or INDEX_PHP_VERSION !== 4) {
	echo '<h1>Bad index version, please update your index.php file!</h1>';
}

// Проверка на допустимую версию PHP.
if (version_compare(PHP_VERSION, '5.2.4') <= 0) {
	die ('Require PHP version 5.2.4 or later.');
}

// Загрузка патчей для совместимости с РНР 5.2.х
if (version_compare(PHP_VERSION, '5.3.0') <= 0) {
	require_once 'php-5.2-patches.php';
}

// Конфиг по умолчанию.
$config = array(
	'action'		 => 'action',
	'admin'			 => 'admin',
	'ajax'			 => 'ajax',
	'cron_key'		 => false,
	'db_name'		 => 'smart_core',
	'db_user'		 => 'root',
	'db_pass'		 => '',
	'db_host'		 => 'localhost',
	'db_lib' 		 => 'PDO',
	'db_port'		 => 3306,
	'db_type'		 => 'mysqli',
	'db_prefix'		 => '',
	'db_persist'	 => false,
	'db_error_send'	 => false,		// Указать емаил на который отправлять ошибки БД.
	'db_cached'		 => 0,
	'http_root'		 => 'auto',
	'dir_lib'		 => 'lib/',
	'dir_lib_zend'	 => 'lib/Zend/',
	'dir_lib_pear'	 => 'lib/pear/',
	'dir_sites'		 => '', 		// "site/" - если задана, то включается режим мультисайтовости и файлы берутся из подпапки ./site/{site_id}/
	'dir_var'		 => 'var/',		// Путь к папкам с техническими переменными (cache, log, tmp, ...)
	'users_base_uri' => 'local',	// URL внешней базы данных пользователей.
	'users_base_key' => false,		// Ключ доступа к внешней базе данных пользователей.
	'debug_error_log'		=> 0,	// Имя файла, например 'php_errors.log'.
	'debug_error_reporting'	=> 0,	// E_ALL | E_STRICT = 8191
	'debug_display_errors'	=> 0,	// Отображать ошибки на экране.
	'debug_db_query'		=> 0,	// Отобразить все выполненные запросы.
	'debug_post_dump'		=> 0,	// Сделать дамп пост данных.
	'debug_profiler'		=> 0,	// Отобразить время выполнения.
	'developer_addresses'	=> '',
	);

// Наложение конфига платформы на системный конфиг.
$config = $cfg_ini + $config;
unset($cfg_ini);

// Определение констант.
define('ACTION',			$config['action']);				// Служебное слово для префикса действий.
define('ADMIN',				$config['admin']);				// Служебное слово для префикса администрирования.
define('AJAX',				$config['ajax']);				// Служебное слово для префикса AJAX.

define('DEBUG_DB_QUERY',	$config['debug_db_query']);		// Логгирование всех запросов в БД.

define('HTTP_HOST',			str_replace('www.', '', $_SERVER['HTTP_HOST'])); // Хост проекта, в формате "site.com" т.е. без префикса "www."

if ($config['http_root'] === 'auto') {						// HTTP корень платформы.
	$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF']; //Full Request URI
	$script_name = $_SERVER['SCRIPT_NAME']; //Script path from docroot
	$base_uri = strpos($request_uri, $script_name) === 0 ? $script_name : str_replace('\\', '/', dirname($script_name));
	if (strlen($base_uri) > 1) { // Не корень веб директории.
		$base_uri .= '/';
	}
	define('HTTP_ROOT',		$base_uri);
} else {
	define('HTTP_ROOT',		$config['http_root']);
}

if (isset($config['http_scripts'])) {						// HTTP путь к папке со скриптами.
	define('HTTP_SCRIPTS',	$config['http_scripts']);
} else {
	define('HTTP_SCRIPTS',	HTTP_ROOT . 'scripts/');
}

if (isset($config['http_sys_resources'])) {					// HTTP путь к папке с системными ресурсными файлами.
	define('HTTP_SYS_RESOURCES',	$config['http_sys_resources']);
} else {
	define('HTTP_SYS_RESOURCES',	HTTP_ROOT . 'resources/');
}

define('DIR_CORE',			DIR_SYSTEM . 'Core/');			// Папка ядра системы.
define('DIR_FRAMEWORK',		DIR_SYSTEM . 'Framework/');		// Путь к фреймворку.
define('DIR_COMPONENTS',	DIR_SYSTEM . 'Components/');	// Путь к компонентам.
define('DIR_MODULES',		DIR_SYSTEM . 'Modules/');		// Путь к модулям.
define('DIR_LIB',			$config['dir_lib']);			// Путь к сторонним библиотекам.
define('DIR_PEAR_CLASSES',	$config['dir_lib_pear']);		// Путь к библиотеке PEAR.
define('DIR_ZEND_FRAMEWORK',$config['dir_lib_zend']);		// Путь к библиотеке Zend Framework.

// Путь к папкам с техническими переменными платформы (cache, log, tmp, ...) (по умолчанию "var/")
if (cmf_is_absolute_path($config['dir_var'])) {
	define('DIR_VAR_PLATFORM',	$config['dir_var']);
} else { // Относительный путь т.е. в папке платформы.
	define('DIR_VAR_PLATFORM',	DIR_ROOT . $config['dir_var']);
}

define('DIR_VAR',			DIR_VAR_PLATFORM . HTTP_HOST . HTTP_ROOT); // Общая папка переменных для запрошенного сайта.
define('DIR_BACKUP',		DIR_VAR . 'backup/');			// Резервные копии.
define('DIR_CACHE',			DIR_VAR . 'cache/');			// Кэш.
define('DIR_LOG',			DIR_VAR . 'log/');				// Логи.
define('DIR_TMP',			DIR_VAR . 'tmp/');				// Темп.

// Установка параметров PHP.
ini_set('include_path', 	'.' . PATH_SEPARATOR . DIR_ZEND_FRAMEWORK . 'library' . PATH_SEPARATOR . DIR_PEAR_CLASSES);
ini_set('error_reporting',	(int) $config['debug_error_reporting']);
ini_set('display_errors',	$config['debug_display_errors']);
ini_set('display_startup_errors', $config['debug_display_errors']);

if (!empty($config['debug_error_log'])) {
	ini_set('error_log',	DIR_LOG . $config['debug_error_log']);
}

// @todo На этапе разработки, некоторые вещи будут описаны во временном файле...
require_once '_temporary.php';

// Регистрация автозагрузчика классов.
require_once DIR_FRAMEWORK . 'Class/Loader.php';
Class_Loader::registerAutoload();
Class_Loader::registerNamespace(array('*' => array(DIR_CORE, DIR_FRAMEWORK, DIR_PEAR_CLASSES, DIR_LIB)) );

// Описание некоторых глобальных функций.

/**
 * Редирект.
 * 
 * @param string $url
 */
function cmf_redirect($url = null)
{
	$str = (null == $url) ? $_SERVER['REQUEST_URI'] : $url;
	header('Location: ' . $str);
	exit;
}

/**
 * Проверить является ли путь абсолютным.
 * 
 * @param string $path
 * @return bool
 */
function cmf_is_absolute_path($path)
{
	return (strpos($path, '/') === 0 or strpos($path, ':') === 1) ? true : false;
}