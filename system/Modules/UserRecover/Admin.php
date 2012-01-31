<?php 
/**
 * Класс с административными методами.
 * 
 * @version 2011-09-21.0
 */
class Module_UserRecover_Admin extends Module_UserRecover implements Admin_ModuleInterface
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
				'label' => 'Нода учетной записи: (обычно в http://site.ru/user/)',
				'type' => 'text',
				'value' => $this->account_node_id,
				),
			'captcha_node_id' => array(
				'label' => 'Нода CAPTCHA: (обычно в http://site.ru/captcha/)',
				'type' => 'text',
				'value' => $this->Node->getParam('captcha_node_id'),
				),
			);
	}
}