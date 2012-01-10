<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Module UserAccount.
 * 
 * @uses EE
 * @uses Node
 * @uses Session
 * @uses User
 * 
 * @package Module
 * @version 2011-09-19.0
 */
class Module_UserAccount extends Module
{
	/**
	 * Включить использование внешних средств авторизации. 
	 * @todo сейчас пока только OpenID 1, потом доделать OpenID 2 и OAuth.
	 */
	protected $enable_openid;
	
	/**
	 * Не обязятельные.
	 */
	protected $profile_node_id;
	protected $recover_node_id;
	protected $register_node_id;
	protected $captcha_node_id;

	/**
	 * Конструктор.
	 * 
	 * @return void
	 */
	protected function init()
	{
		$this->Node->setDefaultParams(array(
			'mode' => 0,
			'profile_node_id' => 0,
			'recover_node_id' => 0,
			'register_node_id' => 0,
			'captcha_node_id' => 0,
			'enable_openid' => 0,
			));
		
		$this->profile_node_id	= $this->Node->getParam('profile_node_id');
		$this->recover_node_id	= $this->Node->getParam('recover_node_id');
		$this->register_node_id	= $this->Node->getParam('register_node_id');
		$this->captcha_node_id	= $this->Node->getParam('captcha_node_id');
		$this->enable_openid	= $this->Node->getParam('enable_openid');
	}
	
	/**
	 * Запуск модуля.
	 * 
	 * @return void
	 */
	public function run($parser_data)
	{
		$Node = new Node();
		
		// Гость
		if ($this->Env->user_id == 0) {
			
			// Пробуем авторизоваться по OpenID
			if ($this->enable_openid and count($_GET) > 0 and (cf_is_get('openid_identity') or cf_is_get('openid_identity'))) {
				$sreg = new Zend_OpenId_Extension_Sreg();
				$consumer = new Zend_OpenId_Consumer();
				if ($consumer->verify($_GET, $id, $sreg)) {
					// echo "VALID " . htmlspecialchars($id);
					// Если пользователь существует, то редирект на папку с профилем юзера.
					if ($this->User->login($_GET['openid_identity'])) {
						cf_redirect();
					}
					// Если пользователя не существует, то предлагается зарегистрироваться.
					else {
						// @todo переделать старт сессии
//						$this->Cookie->cmf_session_start_for_node_id = $this->register_node_id;
						$this->Session->start();
						$this->Session->openid_sreg		= $sreg->getProperties();
						$this->Session->openid_identity = $_GET['openid_identity'];
						// @todo если модуль регистрации не указан - сделать сообщение, что такого юзера нету, иначе редиректнуть на регистрацию с режимом openid
						cf_redirect($this->Node->getUri($this->register_node_id) . '?openid');
					}
				} else {
					// @todo сообщение об ошибке.
					// echo "INVALID " . htmlspecialchars($id);
					cf_redirect($this->Env->current_folder_path);
				}			
			}

			$this->setTpl('AuthForm');
			
			$this->EE->addBreadCrumb('', 'Авторизация');

			// Сформировать ссылку на папку с регистацией
			$this->output_data['register_link'] = $this->register_node_id == 0 ? null : $this->Node->getUri($this->register_node_id);
			
			// Сформировать ссылку на папку с восстановлением пароля
			$this->output_data['recover_link'] = $this->recover_node_id == 0 ? null : $this->Node->getUri($this->recover_node_id);
			
			$this->output_data['auth_form_data'] = $this->getAuthFormData();
			
			if ($this->enable_openid) {
				$this->output_data['auth_openid_form_data'] = $this->getAuthOpenIdFormData();
			}
		}
		// Авторизованный пользователь
		else {
			if (cf_is_get('mode', 'changereg')) {
				$this->changeregMode();
			} elseif (cf_is_get('mode', 'changepass')) {
				$this->changepassMode();
			} else {
				if (cf_is_get('logout')) {
					$this->User->logout();
					cf_redirect($_SERVER['HTTP_REFERER']);
				}
				
				$this->output_data['user_data'] = $this->User->getData();
				
				// Если логин начинается с 'http://', то считается, что это OpenID, иначе нативный логин.
				if (strpos($this->User->getLogin(), 'http://') === false) {
					$this->output_data['password'] = array(
						'title' => 'изменить пароль',
						'change_link' => '?mode=changepass',
						);
				} else {
					$this->output_data['password'] = false;
				}
				
				$this->output_data['welcome_text'] = 'Добро пожаловать';

				$this->output_data['logout'] = array(
					'title' => 'Выход',
					'link' =>  Folder::getUri($this->Node->folder_id) . '?logout',
					'form_element' => 'logout',
					);
				
				$this->output_data['changereg_link'] = '?mode=changereg';
				
				$this->output_data['profile_link'] = $this->profile_node_id != 0 ? $this->Node->getUri($this->profile_node_id) : null;
			}
		}
	}	
	
	/**
	 * Получить данные формы аутентификации через OpenID.
	 *
	 * @param
	 * @return array
	 */
	protected function getAuthOpenIdFormData()
	{
		return array(
			'hiddens' => array( 
				'node_id' => $this->Node->id,
				),
			'elements' => array(
				'pd[openid_identifier]' => array(
					'label' => 'OpenID Login',
					'type' => 'text',
					),
				),
			'buttons' => array(
				'submit[auth_openid]' => array(
					'value' => 'Войти используя OpenID',
					'type' => 'submit',
					),
				),
			'help' => 'Cправка по аутентификации'
			);
	}
	
	/**
	 * Получить данные формы аутентификации.
	 *
	 * @param
	 * @return array
	 */
	protected function getAuthFormData()
	{
		return array(
			'hiddens' => array( 
				'node_id' => $this->Node->id,
				),
			'elements' => array(
				'pd[login]' => array(
					'label' => 'Логин',
					'type' => 'text',
					),
				'pd[pass]' => array(
					'label' => 'Пароль',
					'type' => 'password',
					),
				),
			'autofocus' => 'pd[login]',
			'buttons' => array(
				'submit[auth]' => array(
					'value' => 'Войти',
					'type' => 'submit',
					),
				),
			'help' => 'Cправка по аутентификации'
			);
	}

	/**
	 * Режим изменения пароля.
	 * 
	 */
	protected function changepassMode()
	{
		$this->EE->addBreadCrumb('', 'Изменение пароля');
		$this->setTpl('Changepass');

		if ($this->Session->isKeyExist('update_password_success')) {
			$this->output_data['update_password_success'] = $this->Session->update_password_success;
			$this->Session->deleteKey('update_password_success');
		} else {
			$form_data = array(
				'hiddens' => array( 
					'node_id' => $this->Node->id,
					),
				'elements' => array(
					'pd[old_pass]' => array(
						'label' => 'Старый пароль',
						'type' => 'password',
						),
					'pd[pass1]' => array(
						'label' => 'Новый пароль',
						'type' => 'password',
						),
					'pd[pass2]' => array(
						'label' => 'Подтверждение',
						'type' => 'password',
						),
					),
				'autofocus' => 'pd[old_pass]',
				'buttons' => array(
					'submit[update_password]' => array(
						'value' => 'Обновить пароль',
						'type' => 'submit',
						),
					'submit[cancel]' => array(
						'value' => 'Отмена',
						'type' => 'submit',
						),
					),
				'help' => 'Cправка по изменению пароля'
				);
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
			
			$this->output_data['messages'] = $this->Session->messages;
			$this->output_data['password_form_data'] = $form_data;
		}
		$this->Session->deleteKey('messages');
	}
	
	/**
	 * Режим редактирования персональных данных.
	 *
	 * @param
	 * @return
	 */
	protected function changeregMode()
	{
		$this->EE->addBreadCrumb('', 'Редактирование персональных данных');
		$this->setTpl('Changereg');
		
		$data = $this->User->getData();
		
		$form_data = array(
			'hiddens' => array( 
				'node_id' => $this->Node->id,
				),
			'elements' => array(
				'pd[login]' => array(
					'label' => 'Логин',
					'type' => 'string',
					'value' => $data['login'],
					'disabled' => true,
					),
				'pd[nickname]' => array(
					'label' => 'Псевдоним',
					'type' => 'string',
					'value' => $data['nickname'],
					),
				'pd[fullname]' => array(
					'label' => 'Полное имя',
					'type' => 'string',
					'value' => $data['fullname'],
					),
				'pd[email]' => array(
					'label' => 'Адрес e-mail:',
					'type' => 'string',
					'value' => $data['email'],
					),
				'pd[gender]' => array(
					'label' => 'Пол',
					'type' => 'select',
					'value' => $data['gender'] ? $data['gender'] : 'NA',
					'options' => array(
						'M' => 'мужской',
						'F' => 'женский',
						'NA' => 'не указан',
						),
					),
				'pd[dob]' => array(
					'label' => 'Дата рождения',
					'type' => 'string',
					'title' => 'В формате ГГГГ-ММ-ДД',
					'value' => $data['dob'],
					),
				'pd[language]' => array(
					'label' => 'Язык',
					'type' => 'string',
					'value' => $data['language'],
					),
				'pd[timezone]' => array(
					'label' => 'Часовой пояс',
					'type' => 'string',
					'value' => $data['timezone'],
					),
				),
			'autofocus' => 'pd[nickname]',
			'buttons' => array(
				'submit[update_personal]' => array(
					'value' => 'Сохранить изменения',
					'type' => 'submit',
					),
				'submit[cancel]' => array(
					'value' => 'Отменить',
					'type' => 'submit',
					),
				),
			'help' => 'Cправка по редактированию регистрационных данных'
			);

		$this->output_data['messages'] = $this->Session->messages;
		$this->output_data['personal_form_data'] = $form_data;
		$this->Session->deleteKey('messages');
	}
	
	/**
	 * Обработчик POST данных.
	 * 
	 * @param array $pd
	 * @param string $submit
	 */
	public function postProcessor($pd, $submit)
	{
		switch ($submit) {
			case 'auth':
				if (isset($pd['login']) and isset($pd['pass'])) {
					$this->User->login($pd['login'], $pd['pass']);
				}
				break;
			case 'cancel':
				cf_redirect($this->Env->current_folder_path);
				break;
			case 'update_password':
				$capcha_passed = true;
				if ($this->captcha_node_id) {
					$Node = new Node($this->captcha_node_id);
					if ($pd['captcha_code'] !== $Node->hook('getKeyString')) {
						$this->Session->messages = array(
							'captcha' => 'Неверно введен код с картинки',
							);
						$capcha_passed = false;
					}
				}
				if ($capcha_passed and $this->User->updatePassword($pd['old_pass'], $pd['pass1'], $pd['pass2'])) {
					$this->Session->update_password_success = 'Пароль успешно обновлён';
				}
				cf_redirect();
				break;
			case 'update_personal':
				if ($this->User->updateAccount($pd)) {
					cf_redirect($this->Env->current_folder_path);
				} else {
					cf_redirect();
				}
				break;
			case 'auth_openid':
				if (strlen($pd['openid_identifier']) == 0) {
					cf_redirect();
				}
				// Simple Registration Extension
				$sreg = new Zend_OpenId_Extension_Sreg(array(
					'nickname'	=> false,
					//'fullname'	=> false,
					'email'		=> false,
					//'dob'		=> false,
					//'gender'	=> false,
					//'timezone'	=> false,
					), null, 1.1 );
				
				$consumer = new Zend_OpenId_Consumer();
				if (!$consumer->login($pd['openid_identifier'], $this->Env->current_folder_path, null, $sreg)) {
					// @todo сделать сообщение об ошибке.
					die("OpenID login failed (".$consumer->getError().")");
				}		
				break;
			default;
		}
	}

}