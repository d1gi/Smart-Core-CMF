<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс Session_Handler.
 *  
 * @package		Kernel
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://www.opensource.org/licenses/gpl-2.0
 * 
 * @uses		DB
 * 
 * @version		2012-01-11.0
 *  
 * @todo ОЧЕНЬ важно оптимизировать, чтобы медоты getUserData/setUserData и getToken/setToken 
 *		 выполнялись без запросов! т.е. надо эти данные считывать при первом запуске метода read().
 */
class Session_Handler extends Base
{
	/**
	 * Таблица с сессиями.
	 * @var string
	 */
	private $table = 'sessions';
	
	/**
	 * Время жизни сесии в сеундах.
	 * @var int
	 */
	private $maxlifetime = 1800; // 30 minutes.
	
	/**
	 * Вероятность запуска очистки мусора.
	 * Значение от 1 до 1000.
	 * @var int
	 */
	private $gc_probability = 1;
	
	private $token;
	private $user_data;
	
	/**
	 * Constructor.
	 * 
	 * @param array $cfg
	 */
	public function __construct(array $cfg = null)
	{
//		echo __METHOD__ . " called<br />\n";
		parent::__construct();
		
		if (isset($cfg['maxlifetime']) and is_numeric($cfg['maxlifetime'])) {
			$this->maxlifetime = $cfg['maxlifetime'];
		}
		
		if (isset($cfg['db_table']) and is_string($cfg['db_table'])) {
			$this->table = $cfg['db_table'];
		}
		
		if (isset($cfg['gc_probability']) and is_numeric($cfg['gc_probability'])) {
			$this->gc_probability = $cfg['gc_probability'];
		}
		
		$this->token = false;
		$this->user_data = array(
			'user_id' => 0,
			'groups' => 0,
			);
		
		// Define the probability that the 'garbage collection' process is started on every session initialization.
		// The probability is calculated by using gc_probability/gc_divisor,
		// e.g. 1/100 means there is a 1% chance that the GC process starts on each request.
		ini_set('session.gc_probability', $this->gc_probability);
		ini_set('session.gc_divisor', 1000);
	}
	
	/**
	 * NewFunction
	 *
	 * @param
	 * @return
	 */
	public function getUserData()
	{
//		echo __METHOD__ . " called<br />\n";
		/*
		$id = session_id();
		$sql = "SELECT *
			FROM {$this->table}
			WHERE site_id = '{$this->Env->site_id}'
			AND session_id = '$id'
			AND is_active = '1'
			LIMIT 1
			";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		return unserialize($row->user_data);
		*/
		return $this->user_data;
	}
	
	/**
	 * Получение списка групп пользователя.
	 *
	 * @return array
	 */
	public function getUserGroups()
	{
		return isset($this->user_data['groups']) ? $this->user_data['groups'] : 0;
	}
	
	/**
	 * NewFunction
	 *
	 * @param
	 * @return
	 */
	public function setUserData($data)
	{
//		echo __METHOD__ . " called<br />\n";
		/*
		$data = $this->DB->quote(serialize($data));
		$id = session_id();
		$sql = "
			UPDATE {$this->table} SET
				user_data = $data
			WHERE site_id = '{$this->Env->site_id}'
			AND session_id = '$id'
			AND is_active = '1'
			LIMIT 1
			";
		$this->DB->exec($sql);
		*/
		
		$this->user_data = $data;
		return true;
	}
	
	/**
	 * NewFunction
	 *
	 * @param
	 * @return
	 */
	public function getToken()
	{
//		echo __METHOD__ . " called<br />\n";
		/*
		$id = session_id();
		$sql = "SELECT *
			FROM {$this->table}
			WHERE site_id = '{$this->Env->site_id}'
			AND session_id = '$id'
			AND is_active = '1'
			LIMIT 1
			";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		$this->token = $row->token;
		return $row->token;
		*/
		return $this->token;
	}
	
	/**
	 * NewFunction
	 *
	 * @param
	 * @return
	 */
	public function setToken($token)
	{
//		echo __METHOD__ . " called<br />\n";
		$this->token = $token;
		/*
		$id = session_id();
		$sql = "
			UPDATE {$this->table} SET
				token = '$token'
			WHERE site_id = '{$this->Env->site_id}'
			AND session_id = '$id'
			AND is_active = '1'
			LIMIT 1
			";
		$this->DB->exec($sql);
		return true;
		*/
	}
	
	/**
	 * Open Session.
	 *
	 * @param string $path
	 * @param string $name
	 * @return true
	 */
	public function open($path, $name)
	{
//		echo __METHOD__ . " called<br />\n";
		return true;
	}
	
	/**
	 * Close session.
	 *
	 * @return true
	 */
	public function close()
	{
//		echo __METHOD__ . " called<br />\n";
		return true;
	}

	/**
	 * Read session data.
	 *
	 * @param string $id
	 * @return string
	 */
	public function read($id)
	{
//		echo __METHOD__ . " called<br />\n";
		$return = '';
		
		$sql = "SELECT data, user_data, token
			FROM {$this->table}
			WHERE site_id = '{$this->Env->site_id}'
			AND session_id = '$id'
			AND is_active = 1
			LIMIT 1 ";
		$result = $this->DB->query($sql);
		if ($result->rowCount() == 1) {
			$row = $result->fetchObject();
			
			$return = $row->data;
			// Устанавливается токен и данные юзера.
			$this->user_data = unserialize($row->user_data);
			$this->token = $row->token;
			
			$sql = "
				UPDATE {$this->table} SET
					last_activity_datetime = NOW()
				WHERE site_id = '{$this->Env->site_id}'
				AND session_id = '$id'
				AND is_active = 1
				LIMIT 1 ";
			$this->DB->exec($sql);
		}
		
		return $return;
	}
	
	/**
	 * Write session data.
	 *
	 * @param string $id
	 * @param string $data
	 * @return boolean
	 */
	public function write($id, $data)
	{
//		echo __METHOD__ . " called<br />\n";
		$data = $this->DB->quote($data);
		$user_data = $this->DB->quote(serialize($this->user_data));
		
		// # AND is_active = 1
		$sql = "SELECT session_id 
			FROM {$this->table}
			WHERE site_id = '{$this->Env->site_id}'
			AND session_id = '$id'
			LIMIT 1 ";
		$result = $this->DB->query($sql);
		if ($result->rowCount() == 1) {
			$row = $result->fetchObject();
			// # AND is_active = 1
			$sql = "
				UPDATE {$this->table} SET
					data = $data,
					last_activity_datetime = NOW(),
					user_data = $user_data,
					is_active = 1
				WHERE site_id = '{$this->Env->site_id}'
				AND session_id = '$id'
				LIMIT 1 ";
			return $this->DB->exec($sql);
		} else {
			$sql = "
				INSERT INTO {$this->table}
					(site_id, session_id, create_datetime, last_activity_datetime, data, token, user_data, user_id, user_agent, ip_address)
				VALUES
					('{$this->Env->site_id}', '$id', NOW(), NOW(), $data, '{$this->token}', $user_data, '{$this->user_data['user_id']}', {$this->DB->quote($_SERVER['HTTP_USER_AGENT'])}, {$this->DB->quote($_SERVER['REMOTE_ADDR'])} ) ";
			return $this->DB->exec($sql);
		}
	}
	
	/**
	 * Destroy session.
	 *
	 * @param string $id
	 * @return boolean
	 */
	public function destroy($id)
	{
//		echo __METHOD__ . " called<br />\n";
		$return = false;
		
		$sql = "
			UPDATE {$this->table} SET
				is_active = 0
			WHERE site_id = '{$this->Env->site_id}'
			AND session_id = '$id'
			AND is_active = 1
			LIMIT 1 ";
		if ($this->DB->exec($sql)) {
			$return = true;
		}
		
		return $return;
	}
	
	/**
	 * Garbage Collection.
	 *
	 * @param int $maxlifetime
	 * @return true
	 */
	public function gc($maxlifetime)
	{
//		echo __METHOD__ . " called<br />\n";
		$sql = "DELETE FROM {$this->table}
			WHERE site_id = '{$this->Env->site_id}'
			AND (is_active = 0 OR last_activity_datetime < (NOW() - INTERVAL '{$this->maxlifetime}' SECOND)) ";
		$this->DB->exec($sql);
		return true;
	}
}