<?php
/**
 * Класс по работе с удалённой базой аккаунтов.
 * 
 * Используется только классом User.
 * 
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version 	2011-12-27.0
 */
class User_Account_Remote
{
	// @todo передавать удалённому скрипту.
	//protected $expire = 3888000; // 30 дней
	
	protected $site_id;
	protected $secret_key;
	protected $base_url;

	protected $base_resource;
	
	/**
	 * Текущая версия хеш алгоритма.
	 * @var int
	 */
	protected $current_hash_version;
	
	/**
	 * Constructor.
	 *
	 * @param int $site_id
	 * @param array $config
	 * @return void
	 */
	public function __construct($site_id, $config)
	{
		$this->secret_key = $config['users_base_key'];
		$this->base_url = $config['users_base_uri'];
		$this->site_id = $site_id;
		$this->current_hash_version = 2;
		
		$this->base_resource = curl_init($this->base_url);
		curl_setopt($this->base_resource, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->base_resource, CURLOPT_POST, true);
		//curl_setopt($this->base_resource, CURLOPT_CONNECTTIMEOUT_MS, 2000);
		//curl_setopt($this->base_resource, CURLOPT_PORT, 81);
	}
	
	/**
	 * Отправить запрос на удалённый сервер и получить с него ответ.
	 *
	 * @param array $data
	 * @return array
	 */
	private function request(array $data)
	{
		$data['secret_key'] = $this->secret_key;
		$data['site_id'] = $this->site_id;
		
		curl_setopt($this->base_resource, CURLOPT_POSTFIELDS, $data);
		$string = curl_exec($this->base_resource);
		$response = @unserialize($string);
		
		if (empty($response)) {
			$response = array(
				'status' => 'INVALID',
				'messages' => 'Ошибка ответа сервера',
				'server_response' => $string,
				);
		}
		return $response;
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
		return $this->request(array(
			'method' => 'login',
			'login' => $login,
			'password' => $password,
			));
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
		return $this->request(array(
			'method' => 'logout',
			'token' => $token,
			));
	}
	
	/**
	 * Авторизация по токену.
	 *
	 * @param string $token
	 * @return array
	 */
	public function loginByToken($token)
	{
		return $this->request(array(
			'method' => 'loginByToken',
			'token' => $token,
			));
	}
	
	/**
	 * Проверить существует ли емаил.
	 *
	 * @param string $email
	 * @return bool
	 */
	public function isEmailExist($email, $except_user_id = '')
	{
		return $this->request(array(
			'method' => 'isEmailExist',
			'email' => $email,
			'except_user_id' => $except_user_id,
			));
	}
	
	/**
	 * Проверить существует ли логин.
	 *
	 * @param string $login
	 * @return bool
	 */
	public function isLoginExist($login)
	{
		return $this->request(array(
			'method' => 'isLoginExist',
			'login' => $login,
			));
	}

	/**
	 * Обновление учетной записи.
	 *
	 * @param array $data
	 * @return array
	 */
	public function update($user_id, $pd)
	{
		return $this->request(array(
			'method' => 'update',
			'user_id' => $user_id,
			'pd' => serialize($pd),
			));
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
		return $this->request(array(
			'method' => 'create',
			'pd' => serialize($pd),
			'is_openid' => $is_openid,
			));
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
		return $this->request(array(
			'method' => 'updatePassword',
			'login' => $login,
			'old_pass' => $old_pass,
			'pass1' => $pass1,
			'pass2' => $pass2,
			));
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
		return $this->request(array(
			'method'	=> 'passwordRecoverByEmail',
			'password'	=> $password,
			'email'		=> $email,
			));
	}
}