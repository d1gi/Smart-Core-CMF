<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Модуль профиля пользоватея.
 * 
 * @uses User
 * @uses Node
 * @uses Session
 * 
 * @version 2011-02-06.0
 */
class Module_UserProfile extends Module
{
	protected $user_id;
	
	protected $welcome_node_id;
	protected $login_node_id;

	/**
	 * Конструктор модуля.
	 */
	protected function init()
	{
		$this->user_id = $this->User->getId();
		
		$this->welcome_node_id	= $this->Node->params['welcome_node_id'];
		$this->login_node_id	= $this->Node->params['login_node_id'];
	}

	/**
	 * Запуск модуля.
	 * Профиль пользователя требует авторизации, по этому надо подключать его с правами без чтения для гостей.
	 * 
	 * @todo редактирование всех профилей пользователя.
	 */
	public function run($parser_data)
	{
		$Node = new Node();
		
		// Для гостей переадресация на папку с формой авторизации. Также не отрисовываем форму.
		if ($this->user_id === 0) {
			$login_folder_id = $Node->getProperties($this->login_node_id, 'folder_id');
			if ($this->Env->current_folder_id != $login_folder_id) {
				cmf_redirect(Folder::getUri($login_folder_id));
			}
			$this->output_data['profile_form'] = '';
		}
		// Авторизованный пользователь
		else {
			$status = true;
			$messages = array();
			
			$sql = "SELECT * FROM {$this->DB->prefix()}users WHERE user_id = '{$this->user_id}'";
			$result = $this->DB->query($sql);
			$row = $result->fetchObject();

			$form_data = array(
				// 'target' => '_parent',
				'hiddens' => array( 
					'node_id' => $this->Node->id,
					),
				'elements' => array(
					'pd[login]' => array(
						'label' => 'Логин',
						'type' => 'string',
						'value' => $row->login,
						'disabled' => true,
						),
					'pd[displayed_name]' => array(
						'label' => 'Отображаемое имя',
						'type' => 'string',
						'value' => $row->displayed_name,
						),
					'pd[email]' => array(
						'label' => 'Email',
						'type' => 'string',
						'value' => $row->email,
						),
					'pd[pass1]' => array(
						'label' => 'Изменить пароль',
						'title' => 'Оставьте поля с паролем пустыми, чтобы не изменять его.',
						'type' => 'password',
						),
					'pd[pass2]' => array(
						'label' => 'Повторите пароль',
						'type' => 'password',
						),
					),
				'buttons' => array(
					'submit[save]' => array(
						'value' => 'Сохранить изменения',
						'type' => 'submit',
						),
					'submit[cancel]' => array(
						'value' => 'Отменить',
						'type' => 'submit',
						),
					),
				'help' => 'Cправка по редактированию профилей'
				);

			$this->output_data['messages'] = $this->Session->messages;
			$this->output_data['profile_form_data'] = $form_data;
			$this->Session->deleteKey('messages');
		}
	}	
	
	/**
	 * Обработчик POST данных.
	 * 
	 * @param int $pd
	 * @param string $submit
	 */
	public function postProcessor($pd, $submit)
	{
		$Node = new Node();

		if ($submit === 'cancel') {
			cmf_redirect(Folder::getUri($Node->getProperties($this->welcome_node_id, 'folder_id')));
		}
		
		$status = true;
		$messages = array();
		$form_data = array();

		$displayed_name = trim($pd['displayed_name']);
		$email = trim($pd['email']);

		// Валидация емаила
		$Translator = new Zend_Translate(
			'array',
			DIR_ZEND_FRAMEWORK . '/resources/languages/',
			'ru',
			array('scan' => Zend_Translate::LOCALE_DIRECTORY)
		);
		Zend_Validate_Abstract::setDefaultTranslator($Translator);
		$Validator = new Zend_Validate_EmailAddress();
		
		if ($Validator->isValid($email)) {
			$sql = "SELECT * FROM {$this->DB->prefix()}users WHERE email = '$email' AND user_id != '{$this->user_id}' LIMIT 1";
			$result = $this->DB->query($sql);
			if ($result->rowCount() == 1) {
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
		
		// Валидация отображаемого имени
		if (mb_strlen($displayed_name, 'UTF-8') < 4 or mb_strlen($displayed_name, 'UTF-8') > 20) {
			$messages['displayed_name'] = 'Длина отображаемого имени должна быть не менее 4 и не более 20 символов';
			$status = false;
		}
		
		$displayed_name = trim($this->DB->quote($pd['displayed_name']));
		$email = trim($this->DB->quote($pd['email']));
		
		$password = false;
		if (mb_strlen($pd['pass1']) > 0 or mb_strlen($pd['pass2']) > 0) {
			if (md5($pd['pass1']) == md5($pd['pass2'])) {
				$password = md5($pd['pass1']);
			} else {
				$messages['password'] = 'Пароли не сходятся';
				$status = false;
			}
		}

		if ($password !== false) {
			$pass = ", password = '$password'";
		} else {
			$pass = '';
		}
		
		if (!$status) {
			$this->Session->messages = $messages;
			$this->Session->form_data = $form_data;
			cmf_redirect();
		}
		
		$this->Session->deleteKey('messages');
		$this->Session->deleteKey('form_data');
		
		if ($submit === 'save') {
			// @todo это костыль!!! притом еще и жутко несекьюрный :(
			$data = $this->Session->getUserData();
			$data['name'] = $pd['displayed_name'];
			$data['email'] = $pd['email'];
			$this->Session->setUserData($data);
			
			$sql = "
				UPDATE {$this->DB->prefix()}users SET
					displayed_name = $displayed_name,
					email = $email
					$pass
				WHERE user_id = '{$this->user_id}'
				";
			$this->DB->exec($sql);
			
			// @todo продумать сохранение пароля. ВАЖНО!
			if ($password !== false) {
				$cookie = array();
				$cookie['user_id'] = $this->user_id;
				$cookie['user_pw'] = md5($password);
				
				// тут как пример запоминаем куку на полгода. 
				// @todo надо сделать опцию для времени запоминания куки (это будут общие настройки движка)
				setcookie(COOKIE_NAME, serialize($cookie), time()+60*60*24*90, '/');
			} 
			cmf_redirect(Folder::getUri($Node->getProperties($this->welcome_node_id, 'folder_id')));
		}
	}
	
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		$node_params = array(
			'welcome_node_id' => array(
				'label' => 'Основная нода: (обычно в http://site.ru/user/)',
				'type' => 'text',
				'value' => $this->welcome_node_id,
				),
			'login_node_id' => array(
				'label' => 'Нода авторизации: (обычно в http://site.ru/user/login/)',
				'type' => 'text',
				'value' => $this->login_node_id,
				),
			);
		return $node_params;
	}
	
}