<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Модуль регистрации пользоватея.
 * 
 * @uses Cookie
 * @uses User
 * @uses Node
 * @uses Session
 * @uses Settings
 * 
 * @version 2012-01-14.0
 */
class Module_UserRegistration extends Module
{
	/**
	 * Нода с основной нодой аккаунта юзера.
	 * @var int
	 */
	protected $account_node_id;

	/**
	 * Нода CAPTCHA
	 * @var int
	 */
	protected $captcha_node_id;
	
	/**
	 * Конструктор модуля.
	 */
	protected function init()
	{
		$this->Node->setDefaultParams(array(
			'account_node_id' => 0,
			'captcha_node_id' => 0,
			));
		
		$this->account_node_id	= $this->Node->getParam('account_node_id');
		$this->captcha_node_id	= $this->Node->getParam('captcha_node_id');
	}

	/**
	 * Запуск модуля.
	 */
	public function run($parser_data)
	{
		// Форма регистрации доступна только для гостя.
		if ($this->Env->user_id === 0) { 
			
			// Регистрация через OpenID. 
			if (cf_is_get('openid')) {
				
				// @todo !!!!!!!!!!!
				
//				if ($this->Session->isKeyExist('openid_identity') == false) {
				if ($this->Session_Force->openid_identity == null) {
					cmf_redirect('?');
				}
				
				$login		= $this->Session_Force->openid_identity;
				$nickname 	= '';
				
				if ($this->Session_Force->isKeyExist('openid_sreg')) {
					if (isset($this->Session_Force->openid_sreg['nickname'])) {
						$nickname	= $this->Session_Force->openid_sreg['nickname'];
					}
					if (isset($this->Session_Force->openid_sreg['fullname'])) {
						$fullname	= $this->Session_Force->openid_sreg['fullname'];
					}
					if (isset($this->Session_Force->openid_sreg['email'])) {
						$email	= $this->Session_Force->openid_sreg['email'];
					}
					if (isset($this->Session_Force->openid_sreg['dob'])) {
						$dob	= $this->Session_Force->openid_sreg['dob'];
					}
					if (isset($this->Session_Force->openid_sreg['gender'])) {
						$gender	= $this->Session_Force->openid_sreg['gender'];
					}
					if (isset($this->Session_Force->openid_sreg['timezone'])) {
						$timezone	= $this->Session_Force->openid_sreg['timezone'];
					}
				}

				$form_data = array(
					'hiddens' => array( 
						'node_id' => $this->Node->id,
						),
					'elements' => array(
						'pd[nickname]' => array(
							'label' => 'Псевдоним',
							'type' => 'text',
							'value' => $nickname,
							),
						),
					'autofocus' => 'pd[nickname]',
					'buttons' => array(
						'submit[openid_reg]' => array(
							'value' => 'Зарегистрироваться',
							'type' => 'submit',
							),
						),
					'help' => 'Cправка по регистрации через OpenId'
					);

				if (isset($email) and strlen($email) > 0) {
					$form_data['elements']['pd[email]'] = array(
						'label' => 'Адрес e-mail:',
						'type' => 'text',
						'value' => $email,
						);
				}

				$form_data['elements']['pd[login]'] =  array(
					'label' => 'Логин',
					'type' => 'html',
					'value' => $login,
					);
				
				if (isset($fullname) and strlen($fullname) > 0) {
					$form_data['elements']['pd[fullname]'] = array(
						'label' => 'Полное имя',
						'type' => 'html',
						'value' => $fullname,
						);
				}
				
				if (isset($dob) and strlen($dob) > 0) {
					$form_data['elements']['pd[dob]'] = array(
						'label' => 'Дата рождения',
						'type' => 'html',
						'value' => $dob,
						);
				}
				
				if (isset($gender) and strlen($gender) > 0) {
					$form_data['elements']['pd[gender]'] = array(
						'label' => 'Пол',
						'type' => 'html',
						'value' => ($gender == 'M') ? 'мужской' : 'женский',
						);
				}
			}
			// Нативная регистрация.
			else {
				$login		= $this->Session_Force->form_data['login'];
				$nickname	= $this->Session_Force->form_data['nickname'];
				$email		= $this->Session_Force->form_data['email'];

				$form_data = array(
					'hiddens' => array( 
						'node_id' => $this->Node->id,
						),
					'elements' => array(
						'pd[login]' => array(
							'label' => 'Логин',
							'type' => 'text',
							'value' => $login,
							),
						'pd[nickname]' => array(
							'label' => 'Псевдоним',
							'type' => 'text',
							'value' => $nickname,
							),
						'pd[email]' => array(
							'label' => 'Email',
							'type' => 'text',
							'value' => $email,
							),
						'pd[pass1]' => array(
							'label' => 'Пароль',
							'type' => 'password'
							),
						'pd[pass2]' => array(
							'label' => 'Повторите пароль',
							'type' => 'password'
							),
						),
					'autofocus' => 'pd[login]',
					'buttons' => array(
						'submit[reg]' => array(
							'value' => 'Зарегистрироваться',
							'type' => 'submit',
							),
						),
					'help' => 'Cправка по авторизации'
					);
			}

			if ($this->captcha_node_id) {
				$Node = new Node($this->captcha_node_id);
				$form_data['elements']['pd[captcha_img]'] = array(
					'label' => '',
					'type' => 'html',
					'value' => $Node->hook('getHtmlCode'),
					);
				$form_data['elements']['pd[captcha_code]'] = array(
					'label' => 'Код с картинки',
					'type' => 'text',
					);
			}
			
			$this->View->registration_form_data = $form_data;
			$this->View->messages = $this->Session_Force->messages;
		} else { // Аутентифицированные юзеры редиректятся на главную страничку учетной записи.
			$Node = new Node();
			cmf_redirect($this->Node->getUri($this->account_node_id));
		}
	}	
	
	/**
	 * Обработчик POST данных.
	 * 
	 * @param int $pd
	 * @param string $submit
	 * 
	 * @todo ВАЖНО! сделать адекватную регистрацию с валидацией и сообщениями об ошибках!
	 */
	public function postProcessor($pd, $submit)
	{
		$capcha_passed = true;
		if ($this->captcha_node_id) {
			$Node = new Node($this->captcha_node_id);
			if ($pd['captcha_code'] !== $Node->hook('getKeyString')) {
				$this->Session_Force->messages = array(
					'captcha' => 'Неверно введен код с картинки',
					);
				$capcha_passed = false;
			}
		}
		$Node = new Node();
		switch ($submit) {
			case 'reg':
				if ($capcha_passed and $this->User->createAccount($pd)) {
					$this->User->login(trim($pd['login']), $pd['pass1']);
					cmf_redirect($this->Node->getUri($this->account_node_id));
				} else {
					$this->Session_Force->form_data = array(
						'email' => trim($pd['email']),
						'login' => trim($pd['login']),
						'nickname' => trim($pd['nickname']),
						);
					cmf_redirect();
				}
				break;
			case 'openid_reg':
//				$this->Session->start();
				$nickname = trim($pd['nickname']);
				
				if (isset($pd['email'])) {
					$email = trim($pd['email']);
				} else {
					$email = false;
				}
				
				$pd = $this->Session_Force->openid_sreg;
				$pd['login'] = $this->Session_Force->openid_identity;
				$pd['nickname'] = $nickname;
				$pd['email'] = $email;
				
				if ($capcha_passed and $this->User->createAccount($pd, true)) {
					$this->User->login(trim($pd['login']));
					cmf_redirect($this->Node->getUri($this->account_node_id));
				} else {
					cmf_redirect();
				}
				break;
			default;
		}
	}
}