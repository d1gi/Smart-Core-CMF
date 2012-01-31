<?php
/**
 * Работа с экземплярами сайтов.
 * 
 * @author		Artem Ryzhkov
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @uses		DB
 * @uses		Env
 * @uses		Registry
 * 
 * @version 	2011-12-31.0
 */
class Site extends Base
{
	protected static $_cookie_prefix	 = '';
	protected static $_dir_application	 = 'application/';
	protected static $_is_multi_language = false;
	protected static $_http_lang_prefix	 = '';
	protected static $_robots_txt		 = '';
	protected static $_site_id			 = false;

	/**
	 * Получить список доменов.
	 *
	 * @param int $site_id - ид сайта, по умолчанию системый.
	 * @return array
	 */
	public function getDomainsList($site_id = false)
	{
		if ($site_id === false) {
			$site_id = $this->Env->site_id;
		}
		
		$data = array();
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}engine_sites_domains
			WHERE site_id = '{$site_id}' ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$data[$row->domain] = array(
				'descr' 		  => $row->descr,
				'create_datetime' => $row->create_datetime,
				'language_id'	  => $row->language_id,
				);
		}
		return $data;
	}
	
	/**
	 * Получить ID сайта.
	 * 
	 * @return int
	 */
	public static function getId()
	{
		return self::$_site_id;
	}
	
	/**
	 * Получить robots.txt
	 * 
	 * @return string
	 */
	public static function getRobotsTxt()
	{
		return self::$_robots_txt;
	}
	
	/**
	 * Получить префикс куки.
	 * 
	 * @return string
	 */
	public static function getCookiePrefix()
	{
		return self::$_cookie_prefix;
	}
	
	/**
	 * Получить HTTP_LANG_PREFIX.
	 * 
	 * @return string
	 */
	public static function getHttpLangPrefix()
	{
		return self::$_http_lang_prefix;
	}
	
	/**
	 * Получить путь к папке приложения.
	 * 
	 * @return string
	 */
	public static function getDirApplication()
	{
		return self::$_dir_application;
	}
	
	/**
	 * Получить флаг, является ли сайт мультиязычным.
	 * 
	 * @return bool
	 */
	public static function isMultiLanguage()
	{
		return self::$_is_multi_language;
	}
	
	/**
	 * Инициализация сайта.
	 *
	 * Используется, как статический метод для того, чтобы можно было использовать в Cron для обращения к нодам.
	 *  
	 * @uses DB
	 * @uses EE
	 * 
	 * @param int $site_id - инициализировать заданный site_id
	 * @param string $domain - инициализировать заданный domain @todo 
	 * @return bool
	 * 
	 * @todo обработку входящего $domain.
	 */
	public static function init($site_id = false, $domain = false)
	{
		$DB = Registry::get('DB');
		$Env = Env::getInstance();
		
		// @todo пока так включается сайт по умолчанию, по принципу, самый младший site_id в БД.
		if (empty($Env->dir_sites)) {
			$sql = "SELECT site_id FROM {$DB->prefix()}engine_sites ORDER BY site_id ASC LIMIT 1";
			$site_id = $DB->getRowObject($sql)->site_id;
		}

		if ($site_id === false) {
			$sql = "SELECT site.*, domain.language_id AS domain_language_id, theme.path AS theme_path, theme.content_language, theme.doctype, theme.theme_id
				FROM {$DB->prefix()}engine_sites AS site, {$DB->prefix()}engine_themes AS theme, {$DB->prefix()}engine_sites_domains AS domain
				WHERE domain.domain = '" . HTTP_HOST . "' AND site.theme_id = theme.theme_id AND site.site_id = theme.site_id AND site.site_id = domain.site_id ";
		} else {
			// @todo сейчас если указан $site_id, то не учитывается язык домена. 
			$sql = "SELECT site.*, theme.path AS theme_path, theme.content_language, theme.doctype, theme.theme_id
				FROM engine_sites AS site, engine_themes AS theme
				WHERE site.site_id = '$site_id' AND site.theme_id = theme.theme_id AND site.site_id = theme.site_id ";
		}
		
		$row = $DB->getRowObject($sql);
		if (empty($row)) {
			return false;
		}
		
		$properties = unserialize($row->properties);
		
		if (!empty($properties['session_name'])) {
			session_name($properties['session_name']);
		}

		date_default_timezone_set($properties['timezone']);
		//$DB->exec("'SET TIME_ZONE = '" . date_default_timezone_get() . "'");

		self::$_cookie_prefix		= $properties['cookie_prefix'];
		self::$_is_multi_language	= $properties['multi_language'];
		self::$_robots_txt			= $properties['robots_txt'];
		self::$_site_id				= $row->site_id;
		
		// Установка системного окружения.
		$Env->setVal('site_id', $row->site_id);
		$Env->setVal('language_id', $row->language_id);
		$Env->setVal('default_language_id', $row->language_id);
		$Env->setVal('cache_enable', $properties['cache_enable']);
		
		// Если указан dir_sites, то считается, что применяется мультисайтовый режим.
		if (empty($Env->dir_sites)) {
			$theme_dir = $properties['dir_themes'];
			self::$_dir_application = DIR_ROOT . $properties['dir_application'];
		} else {
			$theme_dir = $Env->dir_sites . $row->site_id . '/' . $properties['dir_themes'];
			self::$_dir_application = DIR_ROOT . $Env->dir_sites . $row->site_id . '/' . $properties['dir_application'];
		}
		
		$Env->setVal('dir_application', self::$_dir_application);
		$Env->setVal('dir_theme', $theme_dir);
		
		// @todo убрать отсюда! :) т.к. далеко не всегда вывод данных будет через шаблонизатор.
		$EE = EE::getInstance();
		$EE->template = array(
			'engine'			=> 'default native php template engine', // @todo поддержку разных шаблонных движков.
			'dir_theme'			=> $theme_dir,			// Папка с темой оформления.
			'theme_name'		=> $row->theme_path,	// Имя темы, фактически является относительной папкой в папке тем. (может быть пустой).
			//'layout'			=> 'main',				// Имя макета, который будет запущен. (Обязателен для шаблонизатора).
			'views'				=> '', 					// Представления.
			'doctype' 			=> $row->doctype,		// @todo убрать отсюда в инишник темы
			'content_language'	=> $row->content_language, // @todo убрать отсюда - язык долже задаваться не в теме а после логики работы ПАРСЕРА!
			'body_attributes'	=> array(), 			// Аттрибуты тега <body>, по умолчанию их нет.
			);
		$EE->head = array(
			'site_short_name'	=> $properties['short_name'],	// Сокрашенное название сайта. // @todo убрать отсюда 
			'site_full_name'	=> $properties['full_name'],	// Полное название сайта. // @todo убрать отсюда 
			);
		return true;
	}
	
	/**
	 * Получить свойства сайта.
	 *
	 * @param int $site_id - ID сайта, по умолчанию системый.
	 * @return array
	 */
	public function getProperties($site_id = false)
	{
		if ($site_id === false) {
			$site_id = $this->Env->site_id;
		}
	
		$sql = "SELECT * FROM {$this->DB->prefix()}engine_sites WHERE site_id = '{$site_id}' ";
		
		if ($row = $this->DB->getRowObject($sql)) {
			$properties = unserialize($row->properties);
			$properties['language_id']		= $row->language_id;
			$properties['theme_id']			= $row->theme_id;
			$properties['create_datetime']	= $row->create_datetime;
			return $properties;
		} else {
			return false;
		}	
	}
}
