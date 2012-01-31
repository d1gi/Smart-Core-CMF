<?php 
/**
 * Logger.
 * 
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF
 * @category	System
 * @package		Kernel
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @uses 		DB
 * @uses 		Env
 * @uses 		Helper_Date
 * @uses 		User
 * 
 * @version		2011-12-27.0
 */	
class Log extends Singleton
{
	private $user_id;
	private $user_login;
	
	private $DB;
	private $Env;
	private $User;
	
	/**
	 * Конструктор. Синглтон паттерн.
	 */
	protected function __construct($user_id = false, $user_login = false)
	{
		$this->DB 	= Registry::get('DB');
		$this->Env	= Env::getInstance();
		$this->User	= Registry::get('User');
		
		if ($user_id === false or $user_login === false) {
			$this->user_id = $this->Env->user_id;
			$this->user_login = $this->User->getLogin();
		} else {
			$this->user_id = $user_id;
			$this->user_login = $user_login;
		}
	}
	
	/**
	 * Записать событие в лог.
	 * 
	 * @param string $type
	 * @param string $message
	 * 
	 * @todo убрать инфу о браузере в класс Env.
	 */
	public function write($type, $message = false) // $stack = false
	{
		$Date = new Helper_Date();
		
		$user_id		= $this->user_id;
		$login			= $this->user_login;
		$message		= $this->DB->quote(trim($message));
		$datetime		= $Date->getDatetime();
		$requested_uri	= $this->DB->quote($_SERVER['REQUEST_URI']);
		$ip				= $this->DB->quote($_SERVER['REMOTE_ADDR']);
		$user_agent		= $this->DB->quote($_SERVER['HTTP_USER_AGENT']);
		
		$referer		= isset($_SERVER['HTTP_REFERER']) ? $this->DB->quote($_SERVER['HTTP_REFERER']) : 'NULL';
		
		$Browser		= new Browser();
		$browser		= $Browser->getBrowser();
		$browser_ver	= $Browser->getVersion();
		$platform		= $Browser->getPlatform();

		switch ($type) {
			case 'http_error':
				$sql = "
					INSERT INTO {$this->DB->prefix()}log_access_errors
						(user_id, site_id, error_code, datetime, requested_uri, ip, referer, user_agent, browser, browser_version, platform)
					VALUES
						('$user_id', '{$this->Env->site_id}', $message, '$datetime', $requested_uri, $ip, $referer, $user_agent, '$browser', '$browser_ver', '$platform') ";
				$this->DB->exec($sql);
				break;
			case 'user_auth':
				$sql = "
					INSERT INTO {$this->DB->prefix()}log_user_auths
						(user_id, site_id, login, action, referer, datetime, ip, user_agent, browser, browser_version, platform)
					VALUES
						('$user_id', '{$this->Env->site_id}', '$login', $message, $referer, '$datetime', $ip, $user_agent, '$browser', '$browser_ver', '$platform') ";
				$this->DB->exec($sql);
				break;
			case 'system':
				$hash = md5($message);
				$sql = "SELECT log_id AS dummy FROM {$this->DB->prefix()}log_system WHERE message_hash = '$hash' AND site_id = '{$this->Env->site_id}' ";
				$result = $this->DB->query($sql);
				if ($result->rowCount() == 0) {
					$sql = "
						INSERT INTO {$this->DB->prefix()}log_system
							(user_id, site_id, message, message_hash, datetime, requested_uri, ip, referer, user_agent, browser, browser_version, platform)
						VALUES
							('$user_id', '{$this->Env->site_id}', $message, '$hash', '$datetime', $requested_uri, $ip, $referer, $user_agent, '$browser', '$browser_ver', '$platform') ";
					$this->DB->exec($sql);
				}
				break;
			default;
		}
		
	}
	
	/**
	 * Поместить запись в системную папку DIR_LOG.
	 *
	 * @param text $msg
	 * @param text $dir - подпапка
	 */
	static public function put($msg, $dir = '')
	{
		$hash = md5($msg);
		
		if (!file_exists(DIR_LOG . $dir)) {
			mkdir(DIR_LOG . $dir, 0777);
		}
		
		if (!file_exists(DIR_LOG .  $dir . $hash . '.txt')) {
			$file = fopen(DIR_LOG . $dir . $hash . '.txt', 'w');
			fwrite($file, $msg);
			fclose($file);
		}
		
		$Date = new Helper_Date();
		
		$file = fopen(DIR_LOG . $dir . $hash . '.log', 'a+');
		fputs($file, $Date->getDatetime() . ' GET: ' . @$_SERVER['REQUEST_URI'] . ', Ref: ' . @$_SERVER['HTTP_REFERER'] . ', UA: ' . @$_SERVER['HTTP_USER_AGENT'] . "\n");
		fclose($file);
	}
}