<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Module приветствия пользователя.
 * 
 * @uses User
 * 
 * @package Module
 * @version 2011-09-22.0
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
			$this->setTpl('Light');
			
			if ($this->register_node_id == 0) {
				$this->output_data['register_link'] = false;
			} else {
				$this->output_data['register_link'] = $this->Node->getUri($this->register_node_id);
			}
			
			if ($this->account_node_id == 0) {
				$this->output_data['login_link'] = false;
			} else {
				$this->output_data['login_link'] = $this->Node->getUri($this->account_node_id);
			}
		}
		// Авторизованный юзер.
		else {
			$this->setTpl('LightWelcome');
			
			$this->output_data['welcome_text'] = 'Добро пожаловать';
			$this->output_data['name'] = $this->User->getName();
			$this->output_data['welcome_link'] = $this->Node->getUri($this->account_node_id);
			
			$this->output_data['logout_link'] = $this->Node->getUri($this->account_node_id) . '?logout';
		}
	}	

}