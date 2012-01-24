<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Модуль восстановление пароля.
 * 
 * @uses User
 * @uses Node
 * @uses Session_Force
 * 
 * @version 2012-01-10.0
 */
class Module_UserRecover extends Module
{
	protected $account_node_id;
	
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
		// Форма восстановления пароля доступна только для гостя.
		if ($this->Env->user_id === 0) {
			if (cf_is_get('key')) {
				$sql = "DELETE FROM {$this->DB->prefix()}users_recover
					WHERE site_id = '{$this->Env->site_id}'
					AND valid_to_datetime < NOW() ";
				$this->DB->exec($sql);
				
				$sql = "SELECT * 
					FROM {$this->DB->prefix()}users_recover
					WHERE site_id = '{$this->Env->site_id}'
					AND code = {$this->DB->quote($_GET['key'])} ";
				$result = $this->DB->query($sql);
				if ($result->rowCount() == 1) {
					$row = $result->fetchObject();
					$form_data = array(
						'hiddens' => array( 
							'node_id' => $this->Node->id,
							),
						'elements' => array(
							'pd[pass1]' => array(
								'label' => 'Новый пароль',
								'type' => 'password',
								),
							'pd[pass2]' => array(
								'label' => 'Подтверждение',
								'type' => 'password',
								),
							),
						'autofocus' => 'pd[pass1]',
						'buttons' => array(
							'submit[update_password]' => array(
								'value' => 'Сменить пароль',
								'type' => 'submit',
								),
							),
						'help' => 'Cправка по восстановлению пароля'
						);
					$this->output_data['update_password_form_data'] = $form_data;
				} else {
					cmf_redirect($this->Node->getUri());
				}
			}
			// Пароль успешно обновлен
			else if ($this->Session_Force->update_password_success == 'PASSED') {
				$this->output_data['update_password_success'] = 'Пароль успешно обновлен, можете войти в систему, используя ваш новый пароль.';
			}
			// в Сессии есть ключ об успешном отправлении емаила.
			else if ($this->Session_Force->send_recover_success == 'PASSED') {
				$this->output_data['send_recover_success'] = 'Информация с инструкциями для восстановления пароля выслана вам на указанный email.';
			}
			// Иначе выводится формочка.
			else {
				$form_data = array(
					'hiddens' => array( 
						'node_id' => $this->Node->id,
						),
					'elements' => array(
						'pd[email]' => array(
							'label' => 'Email', // @todo "или логин"
							'type' => 'string',
							'value' => '',
							),
						),
					'autofocus' => 'pd[email]',
					'buttons' => array(
						'submit[send_recover]' => array(
							'value' => 'Отправить информацию для восстановления',
							'type' => 'submit',
							),
						),
					'help' => 'Cправка по восстановлению пароля'
					);

				$this->output_data['messages'] = $this->Session_Force->messages;
				$this->output_data['recover_form_data'] = $form_data;
			}
		} else { // Авторизнутые юзеры редиректятся на главную страничку авторизации.
			$Node = new Node();
			cmf_redirect($this->Node->getUri($this->account_node_id));
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
		switch ($submit) {
			case 'send_recover': // обработка запроса емаила
				if (trim($pd['email']) == '') {
					return false;
				}
				
				if ($this->User->isEmailExist($pd['email'])) {
					$this->Session_Force->send_recover_success = 'PASSED';
					
					$Date = new Helper_Date();
					$code = hash('sha256', md5(rand(23, 326872387)) . microtime());
					$valid_to_datetime = $Date->getDatetime(mktime(
							date('G'), // hour
							date('i'), // min
							date('s'), // sec
							date('n'), // month
							date('d') + 1, // day
							date('Y') // year
						));
					$sql = "
						INSERT INTO {$this->DB->prefix()}users_recover
							(site_id, code, email, create_datetime, valid_to_datetime )
						VALUES
							('{$this->Env->site_id}', '$code', {$this->DB->quote($pd['email'])}, NOW(), '$valid_to_datetime') ";
					$this->DB->query($sql);

					$recover_link = 'http://' . HTTP_HOST . $this->Node->getUri() . '?key=' . $code;
					
					$data = "Вы запросили восстановление пароля, для этого в течении 24 часов, вам надо пройти по следующей ссылке и ввести новый пароль\n\n";
					$data .= $recover_link;
					
					$mail = new Zend_Mail('UTF-8');
					$mail->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);
					$mail->setBodyText($data);
					$mail->setFrom('password-recover@' . HTTP_HOST, 'Восстановление пароля ' . HTTP_HOST);
					$mail->addTo(trim($pd['email']));
					$mail->setSubject('Восстановление пароля на сайте ' . HTTP_HOST);
					$mail->send();
				} else {
					$this->Session_Force->messages = array('email_not_exist' => 'Данного Email в базе не существует');
				}
				
				break;
			case 'update_password':
				if (!cf_is_get('key')) {
					return false;
				}
				$sql = "DELETE FROM {$this->DB->prefix()}users_recover
					WHERE site_id = '{$this->Env->site_id}'
					AND valid_to_datetime < NOW() ";
				$this->DB->exec($sql);
				
				$sql = "SELECT * 
					FROM {$this->DB->prefix()}users_recover
					WHERE site_id = '{$this->Env->site_id}'
					AND code = {$this->DB->quote($_GET['key'])} ";
				$result = $this->DB->query($sql);
				if ($result->rowCount() == 1) {
					if (md5($pd['pass1']) === md5($pd['pass2'])) {
						$row = $result->fetchObject();
						if ($this->User->passwordRecoverByEmail(trim($pd['pass1']), $row->email)) {
							$this->Session_Force->update_password_success = 'PASSED';
						} else {
							$this->Session_Force->messages = array('server_error' => 'Неудачное обновление пароля (Ошибка сервера)');
						}
					} else {
						$this->Session_Force->messages = array('different_passwords' => 'Пароли не сходятся');
					}
				} else {
					$this->Session_Force->messages = array('key_not_valid' => 'Ключ не валидный.');
				}

				$sql = "DELETE FROM {$this->DB->prefix()}users_recover
					WHERE site_id = '{$this->Env->site_id}'
					AND code = {$this->DB->quote($_GET['key'])} ";
				$this->DB->exec($sql);
				cmf_redirect($this->Node->getUri());
				break;
			default;
		}
	}
}