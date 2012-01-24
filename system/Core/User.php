<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс User.
 * 
 * Предоставляет всем классам интерфейс по работе с пользователям, в свою 
 * очередь в качестве источника учетных записей использует класс UserAccount.
 *  
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @uses 		Cookie
 * @uses 		DB
 * @uses 		Log
 * @uses 		Session
 * @uses 		User_Groups
 * 
 * @version 	2011-12-27.0
 */
class User extends Base
{
	/**
	 * Учетная запись.
	 * @var object
	 */
	private $Account;
	
	/**
	 * Массив с данными пользователя.
	 * @var array
	 */
	private $data;
	
	private $config;
	
	/**
	 * Конструктор. Синглтон паттерн.
	 */
	public function __construct($config = null)
	{
		parent::__construct();
//cmf_dump_backtrace();
		$this->config = $config;
		
		// Инициализация пользователя по умолчанию как "гость".
		$this->data = array (
			'user_id' => 0,
			'login' => '',
			'groups' => false,
			);
		
		// При первой инициализации класса, производится попытка авторизоваться на основе данных из куки. 
		$this->cookieLogin();
	}
	
	/**
	 * Инициализация учетной записи.
	 *
	 * @return bool
	 */
	protected function initAccount()
	{
		if (is_object($this->Account)) {
			return true;
		}
		
		if ($this->config['users_base_uri'] !== 'local') {
			$this->Account = new User_Account_Remote($this->Env->site_id, $this->config);
		} else {
			$this->Account = new User_Account_Local($this->Env->site_id);
		}		
		
		return true;
	}
	
	/**
	 * Авторизация пользователя через Куки.
	 * 
	 * @access private
	 * @return bool
	 */
	protected function cookieLogin()
	{
		if (!is_string($this->Cookie->cmf_token)) {
			return false;
		}
		
		$this->Session->start();
		
		// Если в куки есть ключ сессий, то поднимается сессия и выполняется попытка авторизоваться 
		// на основе данных сессии т.е. без обращения к БД с таблицами пользователей.
		if ($this->Cookie->isSessionNameExist() and $this->Session->getToken() == $this->Cookie->cmf_token) {
			// Сессия с данными существует - выполняется авторизация на основе данных сессии т.е. без запроса в БД к таблице users.
			$this->data = $this->Session->getUserData();
			$this->User_Groups->setUserId($this->data['user_id']);
			$this->Session->setToken($this->Cookie->cmf_token);
			return true;
		} 
		// Токен и ид не совпадают, либо данные сессии пропали, либо ключа сессии нет (возможно был перезапущен браузер)
		// тогда авторизация выполняется через запрос в БД
		else {
			$this->initAccount();
			$result = $this->Account->loginByToken($this->Cookie->cmf_token);
			
			if (isset($result['status']) and $result['status'] === 'VALID') {
				$this->data = $result['data'];
				$this->User_Groups->setUserId($this->data['user_id']);
				$this->data['groups'] = $this->User_Groups->getList($this->data['user_id']);
				
				// Логгеру сразу передаются значения user_id и user_login, чтобы он не циклился т.к. куки авторизация выполняется из конструктора.
				Log::getInstance($result['data']['user_id'], $result['data']['login'])->write('user_auth', 'cookie_login');
				
				$this->Session->setUserData($this->data);
				$this->Session->setToken($this->Cookie->cmf_token);
				return true;
			}
		}
		
		// Данные ид и токена не валидные, выполняется выход, тем самым обнуляются куки и сессии.
		$this->logout(false);
		cmf_redirect();			
	}
	
	/**
	 * Получить ID текущего пользователя.
	 * @return int
	 */
	public function getId()
	{
		return $this->data['user_id'];
	}
	
	/**
	 * Получить login.
	 * @return string
	 */
	public function getLogin()
	{
		return $this->data['login'];
	}
	
	/**
	 * Получить имя пользователя.
	 * 
	 */
	public function getName()
	{
		return $this->data['nickname'];
	}

	/**
	 * Получить е-маил пользователя.
	 * 
	 */
	public function getEmail()
	{
		return $this->data['email'];
	}

	/**
	 * Проверить существует ли емаил в базе аккаунтов.
	 *
	 * @param string $email
	 * @return bool
	 */
	public function isEmailExist($email)
	{
		return $this->Account->isEmailExist($email);
	}
	
	/**
	 * Авторизация пользователя через форму.
	 * 
	 * @access public
	 * @param string $login
	 * @param string $password
	 * @return bool
	 */
	public function login($login, $password = null)
	{
		$this->initAccount();
		$result = $this->Account->login($login, $password);
		
		// Авторизация выполнена успешно.
		if ($result['status'] === 'VALID') {
			$this->Cookie->cmf_token = $result['token'];
			$this->data = $result['data'];
			
			$this->Session->start();
			$this->User_Groups->setUserId($this->data['user_id']);
			
			$this->data['groups'] = $this->User_Groups->getList($this->data['user_id']);
			$this->Session->setUserData($this->data);
			$this->Session->setToken($result['token']);
			
			Log::getInstance()->write('user_auth', 'login');
			
			$this->updateLocal($this->data);
			return true;
		} else {
			// @todo сгенерировать сообщения об ошибке
			return false;
		}
	}	
	
	/**
	 * Выход из системы.
	 * 
	 * @access public
	 * @param bool $is_logging - логгировать выход?
	 * @param int $user_id - форсированно указать user_id
	 * @param string $user_login - форсированно указать user_login
	 * @return void
	 */
	public function logout($is_logging = true, $user_id = 0, $user_login = 0)
	{
		$this->initAccount();
		$this->Account->logout($this->Cookie->cmf_token);
		$this->Cookie->delete('cmf_token');
		$this->Cookie->deleteSessionName();
		$this->Session->destroy();
		if ($is_logging) {
			Log::getInstance($user_id, $user_login)->write('user_auth', 'logout');
		}
	}
	
	/**
	 * Обновление учетной записи.
	 *
	 * @param array $data
	 * @return bool
	 */
	public function updateAccount($pd)
	{
		$this->initAccount();
		$data = $this->Account->update($this->getId(), $pd);
		
		if ($data['status'] === true) {
			$this->updateLocal($data['data']);
			$session_user_data		= $this->Session->getUserData();
			$data['data']['login']	= $session_user_data['login'];
			$data['data']['groups']	= $session_user_data['groups'];
			$this->Session->setUserData($data['data']);
			return true;
		} else {
			$this->Session->messages  = $data['messages'];
			$this->Session->form_data = $data['form_data'];
			return false;
		}
	}
	
	/**
	 * Изменение пароля.
	 *
	 * @param string $old_pass
	 * @param string $pass1
	 * @param string $pass2
	 * @return bool
	 */
	public function updatePassword($old_pass, $pass1, $pass2)
	{
		$this->initAccount();
		$data = $this->Account->updatePassword($this->getLogin(), $old_pass, $pass1, $pass2);
		
		if ($data['status'] == true) {
			return true;
		} else {
			$this->Session->messages = $data['messages'];
			return false;
		}
	}
	
	/**
	 * Создание учетной записи.
	 *
	 * @param array $data
	 * @return array
	 */
	public function createAccount($pd, $is_openid = false)
	{		
		$this->initAccount();
		$data = $this->Account->create($pd, $is_openid);
		if ($data['status'] == true) {
			$this->createLocal($data['data']);
			
//			$session_user_data		= $this->Session->getUserData();
//			$data['data']['login']	= $session_user_data['login'];
			$data['data']['login']	= $pd['login'];
			
			// @todo важно! сделать назначение группы юзеру.
			
			$this->Session->setUserData($data['data']);
			return true;
		} else {
			$this->Session_Force->messages  = $data['messages'];
			$this->Session_Force->form_data = $data['form_data'];
			return false;
		}
	}
	
	/**
	 * Создание локального юзера.
	 *
	 * @param array $pd
	 * @return bool
	 */
	protected function createLocal($pd)
	{
		$user_id	= $pd['user_id'];
		$fullname	= $this->DB->quote(trim($pd['fullname']));
		$nickname	= $this->DB->quote(trim($pd['nickname']));
		$email		= $this->DB->quote(trim($pd['email']));
		$timezone	= $this->DB->quote(trim($pd['timezone']));
		$language	= $this->DB->quote(trim($pd['language']));
		$gender		= $this->DB->quote(trim($pd['gender']));
		$dob		= $this->DB->quote(trim($pd['dob']));
		
		if (strlen($fullname) == 2)	{ $fullname = 'NULL'; }
		if (strlen($email) == 2)	{ $email = 'NULL'; }
		if (strlen($timezone) == 2)	{ $timezone = 'NULL'; }
		if (strlen($gender) == 2)	{ $gender = 'NULL'; }
		if (strlen($dob) == 2)		{ $dob = 'NULL'; }
		if (strlen($language) == 2)	{ $language = 'NULL'; }
				
		$sql = "
			INSERT INTO {$this->DB->prefix()}users_local
				(site_id, user_id, is_active, fullname, nickname, email, timezone, language, gender, dob, create_datetime )
			VALUES
				('{$this->Env->site_id}', '$user_id', '1', $fullname, $nickname, $email, $timezone, $language, $gender, $dob, NOW() ) ";
		$this->DB->query($sql);
		
		// Юзер помещается в группу по умолчанию.
		$sql = "
			INSERT INTO	{$this->DB->prefix()}users_groups_relation
				(user_id, group_id, site_id)
			VALUES
				('$user_id', '{$this->Settings->getParam('default_group_id')}', '{$this->Env->site_id}') ";
		$this->DB->exec($sql);
		return true;
	}
	
	/**
	 * Обновление локального юзера.
	 *
	 * @param array $pd
	 * @return bool
	 */
	protected function updateLocal($pd)
	{
		$user_id	= $pd['user_id'];
		$fullname	= $this->DB->quote(trim($pd['fullname']));
		$nickname	= $this->DB->quote(trim($pd['nickname']));
		$email		= $this->DB->quote(trim($pd['email']));
		$timezone	= $this->DB->quote(trim($pd['timezone']));
		$language	= $this->DB->quote(trim($pd['language']));
		$gender		= $this->DB->quote(trim($pd['gender']));
		$dob		= $this->DB->quote(trim($pd['dob']));
		
		if (strlen($fullname) == 2)	{ $fullname = 'NULL'; }
		if (strlen($email) == 2)	{ $email = 'NULL'; }
		if (strlen($timezone) == 2)	{ $timezone = 'NULL'; }
		if (strlen($gender) == 2)	{ $gender = 'NULL'; }
		if (strlen($dob) == 2)		{ $dob = 'NULL'; }
		if (strlen($language) == 2)	{ $language = 'NULL'; }
		
		// @todo страну и город
//		$country = $this->DB->quote($pd['country']);
//		$city = $this->DB->quote($pd['city']);		
		// # create_datetime = $create_datetime,
		// # country = $country,
		// # city = $city
		$sql = "
			UPDATE {$this->DB->prefix()}users_local SET
				fullname = $fullname,
				nickname = $nickname,
				email = $email,
				timezone = $timezone,
				language = $language,
				gender = $gender,
				dob = $dob,
				last_activity_datetime = NOW()
			WHERE site_id = '{$this->Env->site_id}'
			AND user_id = '$user_id' ";
		$this->DB->query($sql);
		return true;
	}
	
	/**
	 * Получить данные юзера.
	 *
	 * @param int $user_id - если не указан, то возвращаются данные текущего юзера.
	 * @return array
	 */
	public function getData($user_id = null)
	{
		$data = array();

		if ($user_id === null) {
			$data = $this->getId() == 0 ? $this->data : $this->Session->getUserData();
		} else {
			$sql = "SELECT * 
				FROM {$this->DB->prefix()}users_local 
				WHERE user_id = '$user_id'
				AND site_id = '{$this->Env->site_id}' ";
			$result = $this->DB->query($sql);
			$row = $result->fetchObject();
			$data = array(
				'is_active'			=> $row->is_active,
				'create_datetime'	=> $row->create_datetime,
				'user_id'			=> $user_id,
				'fullname'			=> $row->fullname,
				'nickname'			=> $row->nickname,
				'email'				=> $row->email,
				'timezone'			=> $row->timezone,
				'gender'			=> $row->gender,
				'dob'				=> $row->dob,
				'language'			=> $row->language,
				'country'			=> false, // @todo 
				'city'				=> false, // @todo
				// 'last_login_datetime'	 => $row->last_login_datetime,
				'create_datetime' 	=> $row->create_datetime,
				'last_activity_datetime' => $row->last_activity_datetime,
				'groups'			=> $this->User_Groups->getList($user_id),
				);
		}	
		return $data;
	}
	
	/**
	 * Получить список локальных юзеров.
	 *
	 * @param
	 * @return array
	 * 
	 * @todo $items_per_page = 10, $page_num = 1
	 */
	public function getList($items_per_page = 10, $page_num = 1)
	{
		$list = array();
		
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}users_groups_relation
			WHERE site_id = '{$this->Env->site_id}' ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$sql2 = "SELECT * 
				FROM {$this->DB->prefix()}users_local
				WHERE site_id = '{$this->Env->site_id}'
				AND user_id = '{$row->user_id}' ";
			$result2 = $this->DB->query($sql2);
			$row2 = $result2->fetchObject();
			$list[$row->user_id] = array(
				'is_active'			=> $row2->is_active,
				//'login'	  		=> $row2->login,
				'nickname'			=> $row2->nickname,
				'fullname'			=> $row2->fullname,
				'email'				=> $row2->email,
				'timezone'			=> $row2->timezone,
				'gender'			=> $row2->gender,
				'dob'				=> $row2->dob,
				'create_datetime'		=> $row2->create_datetime,
//				'last_login_datetime'	=> $row2->last_login_datetime,
				'last_activity_datetime' => $row2->last_activity_datetime,
				'groups'			=> $this->User_Groups->getList($row->user_id),
				);
		}
		return $list;
	}

	/**
	 * Восстановление пароля для указанного емайла.
	 *
	 * @param string $password
	 * @param string $email
	 * @return bool
	 */
	public function passwordRecoverByEmail($password, $email)
	{
		$this->initAccount();
		$data = $this->Account->passwordRecoverByEmail($password, $email);
		
		if ($data['status'] == true) {
			return true;
		} else {
			$this->Session->messages = $data['messages'];
			return false;
		}
	}
}