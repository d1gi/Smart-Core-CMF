<?php
/**
 * Описание некоторых операций и функций, которые являются временными, их описание и применение надо пересмотреть.
 */

// Проверка на допустимую версию PHP.
if (version_compare(PHP_VERSION, '5.2.4') <= 0) {
	die ('Require PHP version 5.2.4 or later.');
}

// Загрузка патчей для совместимости с РНР 5.2.х
if (version_compare(PHP_VERSION, '5.3.0') <= 0) {
	require_once DIR_SYSTEM . 'php-5.2-patches.php';
}

// включение кеширования страниц целиком для гостей.
define('_IS_CACHE_PAGES', 0);

// включение кеширование нод.
define('_IS_CACHE_NODES', 0);

if (!file_exists(DIR_BACKUP))	mkdir(DIR_BACKUP, 0755, true);
if (!file_exists(DIR_CACHE))	mkdir(DIR_CACHE, 0755, true);
if (!file_exists(DIR_LOG))		mkdir(DIR_LOG, 0755, true);
if (!file_exists(DIR_TMP))		mkdir(DIR_TMP, 0755, true);
if (!file_exists(DIR_VAR_PLATFORM . '.htaccess')) file_put_contents(DIR_VAR_PLATFORM . '.htaccess', "Order Deny,Allow\nDeny from all");

if (!file_exists(DIR_CACHE . 'pages/'))	mkdir(DIR_CACHE . 'pages/', 0755, true);
if (!file_exists(DIR_CACHE . 'nodes/'))	mkdir(DIR_CACHE . 'nodes/', 0755, true);

function exception_handler($exception) {
  echo "Неперехватываемое исключение: " , $exception->getMessage(), "\n";
}

set_exception_handler('exception_handler');

/**
 * Отображение отладочной информации.
 * 
 * @param string $input - данные для отображения.
 * @param string $title - заголовок блока.
 * @param bool $to_file - вывести данные в файл.
 * @return int
 */
function cmf_dump($input, $title = false, $to_file = false)
{
//	cmf_dump_backtrace();
	if (isset($input)) {
		if ($to_file) {
			$handle = fopen('e:\debug.txt', 'a+');
			if($title != false) {
				fwrite($handle, $title . "\n");
			}
			fwrite($handle, print_r($input, true));
			fwrite($handle, "\n============\n");
			fclose($handle);
		} else {
			ob_start();
			echo "\n<pre>";
			
			if($title != false) {
				echo "<hr><b>$title :</b> <br />";
			}
			
			print_r($input);
			
			echo "</pre>\n";
			$output = ob_get_clean();
			$output = str_ireplace('    ', '   ', $output);
			echo($output);
		}
	} else {
		if (!$to_file) {
			echo "\n<pre>\n<b>$title</b> не установлен.\n</pre>\n";
		}
	}
}

/**
 * Обёртка для debug_print_backtrace().
 */
function cmf_dump_backtrace($to_file = false)
{
	if ($to_file) {
		ob_start();
		ob_implicit_flush(false);
	} else {
		echo "<pre>\n";
	}

	debug_print_backtrace();
	
	if ($to_file) {
		$handle = fopen('e:\debug.txt', 'a+');
		fwrite($handle, ob_get_clean());
		fwrite($handle, "\n============\n");
		fclose($handle);
	} else {
		echo "</pre>\n";
	}
}

/**
 * Получение размера файла в удобочитаемом для человека виде.
 *
 * @param file|int $input
 * @param array $metric
 * @return mixed
 * 
 * @todo запаковать в какой-нить класс, а также продумать как лучше реализовать переводы метрик.
 */
function cf_format_filesize($input)
{	
	$metric = array('байт', 'Кб', 'Мб');
	
	if (is_file($input))
		$input = filesize($input);
	
	if ($input < 1024)
		$unit = $metric[0];
	elseif (isset($metric[1]) && (($input = $input / 1024) < 1024))
		$unit = $metric[1];

	elseif (isset($metric[2]) && (($input = $input / 1024) < 1024))
		$unit = $metric[2];

	elseif (isset($metric[3]) && (($input = $input / 1024) < 1024))
		$unit = $metric[3];

	elseif (isset($metric[4]) && (($input = $input / 1024) < 1024))
		$unit = $metric[4];

	return str_replace(' ', '&nbsp;', number_format($input, ($input >= 10) ? 1 : 2, ',', ' ') . " $unit");
}

/**
 * Проверка на существование и соответствие значений $_GET.
 * 
 * @return bool
 */
function cf_is_get($key, $value = false)
{
	if (isset($_GET[$key])) {
		if ($value === false) {
			return true;
		} else if ($_GET[$key] === $value) {
			return true;
		} 
	} 
	return false;
}
