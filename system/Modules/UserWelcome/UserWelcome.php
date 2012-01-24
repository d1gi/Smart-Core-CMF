<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Module приветствия пользователя.
 * 
 * @uses User
 * 
 * @package Module
 * @version 2012-01-14.0
 */
class Module_UserWelcome extends Module
{
	/**
	 * ID Ноды аккаунта.
	 */
	protected $account_node_id;
	
	/**
	 * Конструктор.
	 * 
	 * @return void
	 */
	protected function init()
	{
		$this->Node->setDefaultParams(array(
			'account_node_id' => 0,
			'register_node_id' => 0,
			));
			
		$this->account_node_id	= $this->Node->getParam('account_node_id');
		$this->register_node_id	= $this->Node->getParam('register_node_id');
	}
	
	/**
	 * Запуск модуля.
	 * 
	 * @return void
	 */
	public function run($parser_data)
	{
		if (empty($this->account_node_id)) {
			return null;
		}
		$Node = new Node();
		
		// Гость
		if ($this->Env->user_id === 0) {
			$this->View->setTpl('Light');
			
			if ($this->register_node_id == 0) {
				$this->View->register_link = false;
			} else {
				$this->View->register_link = $this->Node->getUri($this->register_node_id);
			}
			
			if ($this->account_node_id == 0) {
				$this->View->login_link = false;
			} else {
				$this->View->login_link = $this->Node->getUri($this->account_node_id);
			}
		}
		// Авторизованный юзер.
		else {
			$this->View->setTpl('LightWelcome');
			
			$this->View->welcome_text	= 'Добро пожаловать';
			$this->View->name			= $this->User->getName();
			$this->View->welcome_link	= $this->Node->getUri($this->account_node_id);
			$this->View->logout_link	= $this->Node->getUri($this->account_node_id) . '?logout';
		}
	}
}