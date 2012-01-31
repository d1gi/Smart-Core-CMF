<?php 
/**
 * Класс с административными методами.
 * 
 * @version 2011-09-19.0
 */
class Module_UserWelcome_Admin extends Module_UserWelcome implements Admin_ModuleInterface
{
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		return array(
			'account_node_id' => array(
				'label' => 'Основная нода: (обычно в http://site.ru/user/)',
				'type' => 'text',
				'value' => $this->Node->getParam('account_node_id'),
				),
			'register_node_id' => array(
				'label' => 'Нода регистрации новых юзеров: (обычно в http://site.ru/user/register/)',
				'type' => 'text',
				'value' => $this->Node->getParam('register_node_id'),
				),
			);
	}
}