<?php 
/**
 * Класс с административными методами.
 * 
 * @version 2011-09-21.0
 */
class Module_UserAccount_Admin extends Module_UserAccount implements Admin_ModuleInterface
{
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		return array(
			'profile_node_id' => array(
				'label' => 'Нода профиля: (обычно в http://' . HTTP_HOST . HTTP_ROOT . 'user/profile/)',
				'type' => 'text',
				'value' => $this->Node->getParam('profile_node_id'),
				),
			'recover_node_id' => array(
				'label' => 'Нода восстановления логина и пароля: (обычно в http://site.ru/user/recover/)',
				'type' => 'text',
				'value' => $this->Node->getParam('recover_node_id'),
				),
			'register_node_id' => array(
				'label' => 'Нода регистрации новых юзеров: (обычно в http://site.ru/user/register/)',
				'type' => 'text',
				'value' => $this->Node->getParam('register_node_id'),
				),
			'captcha_node_id' => array(
				'label' => 'Нода CAPTCHA: (обычно в http://site.ru/captcha/)',
				'type' => 'text',
				'value' => $this->Node->getParam('captcha_node_id'),
				),
			'enable_openid' => array(
				'label' => 'Включить поддержку OpenID',
				'type' => 'checkbox',
				'value' => $this->Node->getParam('enable_openid'),
				),
			);
	}
}
