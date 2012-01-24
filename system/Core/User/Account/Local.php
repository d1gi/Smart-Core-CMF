<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс по работе с локальной базой аккаунтов, т.е. таблицы users_accounts
 * находятся в в одной базе с инсталляцией платформы.
 * 
 * Используется только классом User.
 * 
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @uses		DB
 * 
 * @version 	2011-12-23.0
 */
class User_Account_Local extends Controller
{
	protected $expire = 3888000; // 30 дней
	
	protected $site_id;

	protected $prefix;
	
	/**
	 * Текущая версия хеш алгоритма.
	 * @var int
	 */
	protected $current_hash_version;
	
	/**
	 * Constructor.
	 *
	 * @param void
	 * @return void
	 */
	public function __construct($site_id)
	{
		parent::__construct();
		$this->site_id = $site_id;
		$this->prefix = '';
		$this->current_hash_version = 2;
	}
	
	/**
	 * Авторизация
	 *
	 * @param string $login
	 * @param string $password
	 * @return array
	 * 
	 * @todo если пароль нулевой, то авторизовывать как OpenID.
	 */
	public function login($login, $password = null)
	{
		// # @todo сделать сообщения типа аккаунт заблокирован и логин заблокирован.
		$sql = "SELECT l.login,
				l.password,
				l.salt,
				l.hash_version,
				a.user_id,
				a.fullname,
				a.nickname,
				a.email,
				a.timezone,
				a.gender,
				a.dob,
				a.language,
				a.create_datetime
			FROM {$this->prefix}users_accounts_logins AS l,
				{$this->prefix}users_accounts AS a
			WHERE l.login = {$this->DB->quote(trim($login))}
			AND l.user_id = a.user_id
			AND a.is_active = 1 
			AND l.is_active = 1 ";
		if ($row = $this->DB->getRowObject($sql)) {
			// Обработка нативных логинов (не OpenID).
			if ($row->hash_version > 0 and (string) $row->password !== (string) $this->getHash($password, $row->hash_version)) {
			//if (this->compareHash($password,$row->password,$row->hash_version))
				$data = array(
					'status'	=> 'INVALID',
					'messages'	=> 'Пароль указан неверно',
					);
				
			} else {
				// Генерация токена
				$token = hash('sha256', md5($password . $row->salt) . microtime());
				//$token = hash('SHA256',substr(hash('SHA1',uniqid(rand().time(), true).rand().microtime()), 0, 32));
				
				$data = array(
					'status' => 'VALID',
					'token' => $token, // Только в случае статуса VALID.
					'data'  => array(
						'login' 	=> $row->login, // Под каким логином в данный момент зашел юзер в свой аккаунт.
						'create_datetime' => $row->create_datetime,
						'user_id'	=> $row->user_id,
						'fullname'	=> $row->fullname,
						'nickname'	=> $row->nickname,
						'email'		=> $row->email,
						'timezone'	=> $row->timezone,
						'gender'	=> $row->gender,
						'dob'		=> $row->dob,
						'language'	=> $row->language,
						'country'	=> '@todo',
						'city'		=> '@todo',
						),
					);
				// Создание записи токена.
				$sql = "
					INSERT INTO {$this->prefix}users_accounts_tokens
						(site_id, user_id, login, token, valid_to_datetime)
					VALUES
						('{$this->site_id}', '{$row->user_id}', " . $this->DB->quote($row->login) . ", " . $this->DB->quote($token) . ", (NOW() + INTERVAL '{$this->expire}' SECOND)) ";
				$this->DB->exec($sql);
			}
			
		} else {
			$data = array(
				'status' => 'INVALID',
				'messages' => 'Данного логина не существует',
				);
		}
		return $data;
	}
	
	/**
	 * Выход. В частности удаляется токен.
	 *
	 * @param string $token
	 * @return bool
	 * 
	 * @todo сделать проверку и адекватное возвращение true/false
	 */
	public function logout($token)
	{
		$sql = "DELETE FROM {$this->prefix}users_accounts_tokens
			WHERE site_id = '{$this->site_id}'
			AND token = " . $this->DB->quote($token) . " ";
		$this->DB->exec($sql);
		return true;
	}
	
	/**
	 * Авторизация по токену.
	 *
	 * @param string $token
	 * @return array
	 */
	public function loginByToken($token)
	{
		// # l.salt,
		$sql = "SELECT l.login,
				a.user_id,
				a.fullname,
				a.nickname,
				a.email,
				a.timezone,
				a.gender,
				a.dob,
				a.language,
				a.create_datetime
			FROM {$this->prefix}users_accounts_tokens AS t,
				{$this->prefix}users_accounts_logins AS l,
				{$this->prefix}users_accounts AS a
			WHERE t.site_id = '{$this->site_id}'
			AND t.token = " . $this->DB->quote($token) . "
			AND t.login = l.login
			AND t.user_id = a.user_id
			AND l.user_id = a.user_id
			AND a.is_active = 1
			AND l.is_active = 1 ";
		$result = $this->DB->query($sql);
		if ($result->rowCount() == 1) {
			$row = $result->fetchObject();
			$data = array(
				'status' => 'VALID',
				// @todo сделать "системную соль" которая будет задаваться для каждого сайта отдельно.
				'token' => $token, // Только в случае статуса VALID.
				'data'  => array(
					'login' 	=> $row->login, // Под каким логином в данный момент зашел юзер в свой аккаунт.
					'create_datetime' => $row->create_datetime,
					'user_id'	=> $row->user_id,
					'fullname'	=> $row->fullname,
					'nickname'	=> $row->nickname,
					'email'		=> $row->email,
					'timezone'	=> $row->timezone,
					'gender'	=> $row->gender,
					'dob'		=> $row->dob,
					'language'	=> $row->language,
					'country'	=> '@todo',
					'city'		=> '@todo',
					),
				);
			return $data;
		} else {
			$data = array(
				'stаtus' => 'INVALID',
				'messages' => '', // Сообщения об ошибка, если статус INVALID.
				);
			return $data;
		}
	}
	
	/**
	 * Обновление учетной записи.
	 *
	 * @param array $data
	 * @return array
	 */
	public function update($user_id, $pd)
	{
		$return		= array();
		$status		= true;
		$messages	= array();
		$form_data	= array();
		
		$nickname	= trim($pd['nickname']);
		$fullname	= trim($pd['fullname']);
		$timezone	= trim($pd['timezone']);
		$dob		= trim($pd['dob']);
		$gender		= trim($pd['gender']);
		$language	= trim($pd['language']);
		$email		= trim($pd['email']);
		
		// Валидация емаила
		$Validator = new Helper_Validator();
		
		if ($Validator->email($email)) {
			if ($this->isEmailExist($email, $user_id)) {
				$messages['email'] = "Емаил <b>$email</b> уже занят";
				$status = false;
			}
		} else {
			$tmp = '';
			foreach ($Validator->getMessages() as $msg) {
				$tmp .= "$msg<br />\n";
			}
			$messages['email'] = $tmp;
			$status = false;
		}

		// Валидация псевдонима
		if (mb_strlen($nickname, 'UTF-8') < 4 or mb_strlen($nickname, 'UTF-8') > 20) {
			$messages['nickname'] = 'Длина псевдонима должна быть не менее 4 и не более 20 символов';
			$status = false;
		}
		
		// Валидация полного имени
		if (mb_strlen($fullname, 'UTF-8') < 4 or mb_strlen($fullname, 'UTF-8') > 100) {
			$messages['fullname'] = 'Длина полного имени должна быть не менее 4 и не более 100 символов';
			$status = false;
		}
		
		// Проверка пола
		if (strtoupper($gender) === 'F') {
			$gender = "'F'";
		} else if (strtoupper($gender) === 'M') {
			$gender = "'M'";
		} else {
			$gender = 'NULL';
		}
		
		// Проверка языка
		$language = strlen($language) == 2 ? $this->DB->quote($language) : 'NULL';
		
		// Проверка даты рождения.
		$dob = strlen($dob) == 0 ? 'NULL' : $this->DB->quote($dob);
		
		// Проверка часового пояса.
		$timezone = strlen($timezone) == 0 ? 'NULL' : $this->DB->quote($timezone);
		
		$return['status'] = $status;
		
		// Если статус положительный то производится собственно обновление.
		if ($status === true) {
			$sql = "
				UPDATE {$this->prefix}users_accounts SET
					fullname = {$this->DB->quote($fullname)},
					nickname = {$this->DB->quote($nickname)},
					email = {$this->DB->quote($email)},
					timezone = $timezone,
					gender = $gender,
					dob = $dob,
					language = $language
				WHERE user_id = '$user_id' ";
			$this->DB->exec($sql);
			$return['data'] = $this->getData($user_id);
		} else {
			$return['messages'] = $messages;
			$return['form_data'] = $form_data;
		}
		
		return $return;
	}
	
	/**
	 * Создание учетной записи.
	 *
	 * @param array $data
	 * @param bool $is_openid - по умолчанию содатёся не OpenID, а нативный логин а именно с использованием пароля.
	 * @return array
	 */
	public function create($pd, $is_openid = false)
	{
		$return 	= array();
		$status		= true;	
		$password	= false;
		$messages	= array();
		
		$login		= trim($pd['login']);
		$nickname	= trim($pd['nickname']);
		$email		= trim($pd['email']);

		$form_data = array(
			'login'	=> $login,
			'email'	=> $email,
			'nickname' => $nickname,
			);
		
		// Валидация логина на допустимые символы и на уникальность и длинну.
		if ($is_openid == false and !preg_match("/^[a-z_0-9.-]{4,20}/i", $login)) {
			$messages['login_format'] = 'Логин может состоять только из латинских букв, цифр и сивола подчеркивания от 4 до 20 символов';
			$status = false;
		} else {
			if ($this->isLoginExist($login)) {
				$messages['login_used'] = 'Данный логин уже занят';
				$status = false;
			}
		}
		
		// Валидация псевдонима
		if (mb_strlen($nickname, 'UTF-8') < 4 or mb_strlen($nickname, 'UTF-8') > 20) {
			$messages['nickname'] = 'Длина псевдонима должна быть не менее 4 и не более 20 символов ';
			$status = false;
		}
		
		// Валидация емаила
		if ($is_openid == false or strlen($email) > 0) {
			$Validator = new Helper_Validator();
			if ($Validator->email($email)) {
				if ($this->isEmailExist($email)) {
					$messages['email'] = 'Данный емаил уже занят';
					$status = false;
				}
			} else {
				$messages = array_merge($messages, $Validator->getMessages());
				$status = false;
			}
			
			$email = $this->DB->quote($email);
		} else {
			$email = 'NULL';
		}
		
		// Проверка паролей.
		if ($is_openid == false) {
			if (strlen($pd['pass1']) > 0 and strlen($pd['pass2']) > 0 and md5($pd['pass1']) == md5($pd['pass2'])) {
				$password = $this->getHash($pd['pass1']);
			} else {
				$messages['password'] = 'Пароли не сходятся';
				$status = false;
			}
		}
		
		$return['status'] = $status;
		
		// ------------------
		
		if (!$status) {
			$return['messages'] = $messages;
			$return['form_data'] = $form_data;
		}
		// Если статус положительный и пароли сходятся, то производится создание аккаунта.
		else {
			$login 		= trim($this->DB->quote($pd['login']));
			$nickname	= trim($this->DB->quote($pd['nickname']));
			
			// dob, gender, fullname, language, timezone
			
			$sql = "
				INSERT INTO	{$this->prefix}users_accounts
					(nickname, email, create_datetime, create_on_site_id)
				VALUES
					($nickname, $email, NOW(), '{$this->site_id}') ";
			$this->DB->query($sql);

			// @todo пока создаётся сразу активный логин, потом сделать настройку выполнять ли подтверждение по Email.
			$user_id = $this->DB->lastInsertId();
			
			if ($is_openid) {
				$salt = 'NULL';
				$password = 'NULL';
				$hash_version = 0;
			} else {
				$salt = $this->DB->quote(hash('crc32', md5($password) . microtime()));
				$password = $this->DB->quote($password);
				$hash_version = $this->current_hash_version;
			}
			
			$sql = "
				INSERT INTO	{$this->prefix}users_accounts_logins
					(is_active, user_id, login, password, salt, hash_version, create_datetime, create_on_site_id)
				VALUES
					('1', '$user_id', $login, $password, $salt, '$hash_version', NOW(), '{$this->site_id}') ";
			$this->DB->exec($sql);

			$return['data'] = $this->getData($user_id);
		}
		
		return $return;
	}
	
	/**
	 * Проверить существует ли емаил.
	 *
	 * @param string $email
	 * @return bool
	 */
	public function isEmailExist($email, $except_user_id = '')
	{
		if ($except_user_id !== '') {
			$except_user_id = " AND user_id != '$except_user_id' ";
		}
		$result = $this->DB->query("SELECT user_id AS dummy FROM {$this->prefix}users_accounts WHERE email = {$this->DB->quote(trim($email))} $except_user_id ");
		
		return $result->rowCount() ? true : false;
	}
	
	/**
	 * Проверить существует ли логин.
	 *
	 * @param string $login
	 * @return bool
	 */
	public function isLoginExist($login)
	{
		$sql = "SELECT user_id AS dummy FROM {$this->prefix}users_accounts_logins WHERE login = {$this->DB->quote(trim($login))}";
		$result = $this->DB->query($sql);
		
		return $result->rowCount() ? true : false;
	}
	
	/**
	 * Изменение пароля.
	 *
	 * @param string $old_pass
	 * @param string $pass1
	 * @param string $pass2
	 * @return array
	 */
	public function updatePassword($login, $old_pass, $pass1, $pass2)
	{
		$return 	= array();
		$status		= true;
		$password	= false;
		$messages	= array();
		
		// 1. Проверка старого пароля.
		/*
		$login = $this->DB->quote($login);
		$sql = "SELECT user_id
			FROM {$this->prefix}users_accounts_logins
			WHERE login = $login
			AND password = '" . md5($old_pass) . "'
			";
		$result = $this->DB->query($sql);
		if ($result->rowCount() == 0) {
			$messages['old_password'] = 'Неверно введен старый пароль';
			$status	= false;
		}
		*/
		$login = $this->DB->quote($login);
		$sql = "SELECT user_id, password, hash_version
			FROM {$this->prefix}users_accounts_logins
			WHERE login = $login
			AND hash_version > 0 ";
		$result = $this->DB->query($sql);
		if ($result->rowCount() == 0) {
			$messages['wrong_login'] = 'Такого логина не существует';
			$status	= false;
		} else {
			$row = $result->fetchObject();
			if ($row->password != $this->getHash($old_pass, $row->hash_version)) {
				$messages['old_password'] = 'Неверно введен старый пароль';
				$status	= false;
			}
		}
		// 2. @todo Проверка на допустимость использования пароля.
		
		// 3. Проверка подтверрждение пароля.
		if (strlen($pass1) > 0 and strlen($pass2) > 0 and md5($pass1) == md5($pass2)) {
			$password = $this->DB->quote($this->getHash($pass1));
		} else {
			$messages['different_passwords'] = 'Пароли не сходятся';
			$status	= false;
		}

		$return['status'] = $status;
		
		// Если есть ошибки
		if ($status === false) {
			$return['messages'] = $messages;
		} else {
			// 4. Обновление пароля.
			$sql = "
				UPDATE {$this->prefix}users_accounts_logins SET
					password = $password,
					hash_version = '{$this->current_hash_version}'
				WHERE login = $login ";
			$this->DB->query($sql);
		}
	
		return $return;
	}
	
	/**
	 * Извлечение данных об аккаунте.
	 *
	 * @param int $user_id
	 * @return array|false
	 */
	public function getData($user_id)
	{
		$sql = "SELECT * 
			FROM {$this->prefix}users_accounts
			WHERE user_id = '$user_id' ";
		$result = $this->DB->query($sql);
		if ($result->rowCount() == 1) {
			$row = $result->fetchObject();
			$data = array(
				'user_id'	=> $row->user_id,
				'fullname'	=> $row->fullname,
				'nickname'	=> $row->nickname,
				'email'		=> $row->email,
				'timezone'	=> $row->timezone,
				'gender'	=> $row->gender,
				'dob'		=> $row->dob,
				'language'	=> $row->language,
				'country'	=> '@todo',
				'city'		=> '@todo',
				);
			return $data;
		} else {
			return false;
		}
	}
	
	/**
	 * Генерация хэша пароля.
	 *
	 * @param string $password
	 * @param int $version
	 * @return string
	 */
	protected function getHash($password, $version = false)
	{
		$return = '';
		if ($version === false) {
			$version = $this->current_hash_version;
		}
		
		switch ($version) {
			// Обычный md5.
			case 1:
				$return = md5($password);
				break;
			// Двойной md5.
			case 2:
				$return = md5(md5($password));
				break;
			/*
			case 3:
			//хэш вида $1$c6b194b2$uKRGa4ype0kG3XgbYnkDC0
			//MD5
				$seed='$1$'.substr(hash('MD5',uniqid(rand().time(), true).rand().microtime()), 0, 8);
				$return = crypt($password,$seed);
				break;
			case 4:
			//хэш вида $5$5d649737$pZss4G3E3WNiESia3qV9p5DFOZW8BqBJ1CXYhoooeuB
			//SHA256
				$seed='$5$'.substr(hash('MD5',uniqid(rand().time(), true).rand().microtime()), 0, 8);
				$return = crypt($password,$seed);
				break;
			case 5:
			//хэш вида $5$rounds=3810$f33214a144637234$y1dpJByCdU3cM90Gh0hemMCFwmBmtAiSF5Or5b.gHA9
			//SHA256
				$seed='$5$rounds='.rand(1000,5000).'$'.substr(hash('MD5',uniqid(rand().time(), true).rand().microtime()), 0, 16);
				$return = crypt($password,$seed);
				break;
			*/
			default;
		}
	
		return $return;
	}
	
	/**
	 * Сравнение хэша и пароля.
	 *
	 * @param string $password
	 * @param string $hash
	 * @param int $version
	 * @return bool
	 */
	protected function compareHash($password,$hash,$version = false)
	{
		if ($version === false) {
			$version = $this->current_hash_version;
		}
		
		switch ($version)
		{
			case 1:
				if (md5($password) == $hash) return true;
				else return false;
			case 2:
				if (md5(md5($password)) == $hash) return true;
				else return false;
			/*
			case 3:
				if (crypt($password,$hash) == $hash) return true;
				else return false;
			case 4:
				if (crypt($password,$hash) == $hash) return true;
				else return false;
			case 5:
				if (crypt($password,$hash) == $hash) return true;
				else return false;
			*/
			default;
		}
		return false;
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
		$return = array();
		
		$sql = "SELECT * 
			FROM {$this->prefix}users_accounts
			WHERE email = {$this->DB->quote($email)} ";
		$result = $this->DB->query($sql);
		if ($result->rowCount()) {
			$row = $result->fetchObject();
			$sql = "SELECT * 
				FROM {$this->prefix}users_accounts_logins
				WHERE user_id = '{$row->user_id}'
				AND hash_version > 0 ";
			$result = $this->DB->query($sql);
			if ($result->rowCount()) {
				$row = $result->fetchObject();
				
				$password = $this->DB->quote($this->getHash($password));
				$sql = "
					UPDATE {$this->prefix}users_accounts_logins SET
						password = $password,
						hash_version = '{$this->current_hash_version}'
					WHERE user_id = '{$row->user_id}'
					AND hash_version > 0 ";
				$this->DB->query($sql);
				
				$return['status'] = true;
			} else {
				$return['status'] = false;
				$return['messages'] = 'Неверный ID пользователя';
			}
		} else {
			$return['status'] = false;
			$return['messages'] = 'Нет такого email';
		}
		
		return $return;
	}
}