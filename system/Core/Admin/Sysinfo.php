<?php
/**
 * Системаная информация.
 * 
 * @author	Artem Ryzhkov
 * @package	Kernel
 * 
 * @uses	Kernel
 * 
 * @version 2012-01-24.0
 */
class Admin_Sysinfo extends Controller
{
	/**
	 * Constructor.
	 *
	 * @param void
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->View->setTpl('sysinfo');
	}

	/**
	 * Action....
	 * 
	 * @param string $uri_path
	 * @return array
	 */
	public function run($path)
	{
		$this->View->menu = array(
			'platform' => array(
				'uri'	=> '',
				'title' => 'Платформа',
				'descr'	=> '',
				),
			'php' => array(
				'uri'	=> 'php/',
				'title' => 'Конфигурация PHP',
				'descr'	=> '',
				),
			'phpinfo' => array(
				'uri'	=> 'phpinfo/',
				'title' => 'PHP Информация',
				'descr'	=> '',
				),
			);
		
		$path_parts = explode('/', $path);
		switch ($path_parts[0]) {
			case 'phpinfo':
				$this->View->menu['phpinfo']['selected'] = true;
				ob_start();
				phpinfo();
				$this->View->phpinfo = ob_get_clean();
				break;
			case 'php':
				$this->View->php_settings = $this->getPhpSettings();
				$this->View->menu['php']['selected'] = true;
				break;
			case 'platform':
			default;
				$this->View->platform = $this->getPlatformInfo();
				$this->View->menu['platform']['selected'] = true;
		}
	}
	
	/**
	 * Получить информацию о платформе.
	 *
	 * @return array
	 */
	public function getPlatformInfo()
	{
		$data = array();
		// Smart Core Version
		$data[] = array(
			'title' => 'Smart Core CMF Version',
			'value' => 'v' . Kernel::VERSION . ' build ' . Kernel::VERSION_BUILD . ' (' . Kernel::VERSION_DATE . ')', // (http://smart-core.org)
			'required' => '',
			'recomended' => '',
			'hint' => '',
			'warning' => 0,
			);		
		// Database server
		$sql = 'SHOW VARIABLES LIKE "%version_comment%"';
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		$dbserver = $row->Value;
		$data[] = array(
			'title' => 'Database server',
			'value' => $dbserver,
			'required' => 'MySQL',
			'recomended' => 'MySQL',
			'hint' => '',
			'warning' => 0,
			);
		// Database version
		$sql = "SELECT version() AS version";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		$version = $row->version;
		$data[] = array(
			'title' => 'Database version',
			'value' => $version,
			'required' => '5.0',
			'recomended' => '5.5',
			'hint' => '',
			'warning' => 0,
			);
		// PHP Built On:
		$data[] = array(
			'title' => 'PHP Built On:',
			'value' => php_uname(),
			'required' => '',
			'recomended' => '',
			'hint' => '',
			'warning' => 0,
			);
		// PHP Version
		$data[] = array(
			'title' => 'PHP Version',
			'value' => phpversion(),
			'required' => '5.2.4',
			'recomended' => '5.3.8',
			'hint' => '',
			'warning' => 0,
			);
		// Web Server
		$data[] = array(
			'title' => 'Web Server',
			'value' => $_SERVER["SERVER_SOFTWARE"],
			'required' => '',
			'recomended' => '',
			'hint' => '',
			'warning' => 0,
			);
		// WebServer to PHP Interface
		$data[] = array(
			'title' => 'WebServer to PHP Interface',
			'value' => php_sapi_name(),
			'required' => '',
			'recomended' => '',
			'hint' => '',
			'warning' => 0,
			);
		// Zend_Framework
		$data[] = array(
			'title' => 'Zend Framework version',
			'value' => Zend_Version::VERSION, // . ' (' . DIR_ZEND_FRAMEWORK . ')',
			'required' => '1.11.11',
			'recomended' => @Zend_Version::getLatest(),
			'hint' => '',
			'warning' => 0,
			);
		return $data;
	}
	
	/**
	 * Получить настрйоки PHP.
	 *
	 * @return array
	 */
	public function getPhpSettings()
	{
		$data = array();
		// PHP Version
		$data[] = array(
			'title' => 'PHP Version',
			'value' => phpversion(),
			'required' => '5.2.4',
			'recomended' => '5.3.8',
			'hint' => '',
			'warning' => 0,
			);
		// Memory limit
		$data[] = array(
			'title' => 'Memory Limit',
			'value' => ini_get('memory_limit'),
			'required' => '64M',
			'recomended' => '128M',
			'hint' => '',
			'warning' => 0,
			);
		// Safe Mode
		if (ini_get('safe_mode')) {
			$value = 'On';
		} else {
			$value = 'Off';
		}
		$data[] = array(
			'title' => 'Safe Mode',
			'value' => $value,
			'required' => 'Off',
			'recomended' => 'Off',
			'hint' => '',
			'warning' => 0,
			);
		// Display Errors
		if (ini_get('display_errors')) {
			$value = 'On';
		} else {
			$value = 'Off';
		}
		$data[] = array(
			'title' => 'Display Errors',
			'value' => $value,
			'required' => 'Off',
			'recomended' => 'Off',
			'hint' => '',
			'warning' => 0,
			);
		// Magic Quotes
		if (ini_get('magic_quotes_gpc')) {
			$value = 'On';
		} else {
			$value = 'Off';
		}
		$data[] = array(
			'title' => 'Magic Quotes',
			'value' => $value,
			'required' => 'Off',
			'recomended' => 'Off',
			'hint' => '',
			'warning' => 0,
			);
		// Register Globals
		if (ini_get('register_globals')) {
			$value = 'On';
		} else {
			$value = 'Off';
		}
		$data[] = array(
			'title' => 'Register Globals',
			'value' => $value,
			'required' => 'Off',
			'recomended' => 'Off',
			'hint' => '',
			'warning' => 0,
			);
		// Output Buffering
		if ((bool)ini_get('output_buffering')) {
			$value = 'On';
		} else {
			$value = 'Off';
		}
		$data[] = array(
			'title' => 'Output Buffering',
			'value' => $value,
			'required' => 'On',
			'recomended' => '',
			'hint' => '',
			'warning' => 0,
			);
		// Mbstring Enabled
		if (extension_loaded('mbstring')) {
			$value = 'Yes';
		} else {
			$value = 'No';
		}
		$data[] = array(
			'title' => 'Mbstring Enabled',
			'value' => $value,
			'required' => 'Yes',
			'recomended' => '',
			'hint' => '',
			'warning' => 0,
			);
		// Upload_max_filesize
		$data[] = array(
			'title' => 'Upload max filesize',
			'value' => ini_get('upload_max_filesize'),
			'required' => '4M',
			'recomended' => '20M',
			'hint' => '',
			'warning' => 0,
			);
		return $data;
	}
}