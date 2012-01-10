<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Турбар.
 * 
 * @uses Helper_Head
 * @uses Settings
 * 
 * @version 2011-07-13.0
 */
class Admin_Toolbar extends Base
{
	/**
	 * Конструктор.
	 */
	public function __construct()
	{
		$Popup = new Component_Popup();
		
		$this->EE->useScriptLib('jquery');
		
		$this->EE->addHeadScript('superfish',	HTTP_SYS_RESOURCES . 'admin/toolbar/superfish.js');
		$this->EE->addHeadStyle('superfish',	HTTP_SYS_RESOURCES . 'admin/toolbar/superfish.css');
		
		$this->EE->addHeadStyle('cmf_toolbar',	HTTP_SYS_RESOURCES . 'admin/toolbar/toolbar.css');
		$this->EE->addHeadScript('cmf_toolbar',	HTTP_SYS_RESOURCES . 'admin/toolbar/toolbar.js');
	}
	
	/**
	 * Отрисовка тулбара.
	 */
	public function render()
	{
		// @todo нормальное меню тулбара.
		/*
		$Menu = array(
			'Структура' =
			);
		*/
		
		$current_folder_id = Kernel::getEnv()->current_folder_id;
		
		$edit_folder = array(
			'title' => 'Редактировать папку',
			'link' => HTTP_ROOT . ADMIN . '/structure/folder/' . $current_folder_id . '/?popup'
			);
		
		$new_folder = array(
			'title' => 'Добавить папку',
			'link' => HTTP_ROOT . ADMIN . '/structure/folder/create/' . $current_folder_id . '/?popup'
			);
		
		$all_folders = array(
			'title' => 'Вся структура папок',
			'link' => HTTP_ROOT . ADMIN . '/structure/folder/'
			);

		$new_module = array(
			'title' => 'Добавить модуль (ноду)',
			'link' => HTTP_ROOT . ADMIN . '/structure/node/create/' . $current_folder_id . '/?popup'
			);
		
		$all_nodes = array(
			'title' => 'Все модули в текущей папке',
			'link' => HTTP_ROOT . ADMIN . '/structure/node/in_folder/' . $current_folder_id . '/'
			);
		$users = array(
			'title' => 'Пользователи',
			'link' => HTTP_ROOT . ADMIN . '/users/',
			);
		$settings = array(
			'title' => 'Глобальная конфигурация',
			'link' => HTTP_ROOT . ADMIN . '/config/global/',
			);
		$site = array(
			'title' => 'Сайт',
			'link' => HTTP_ROOT . ADMIN . '/config/site/',
			);
		$containers = array(
			'title' => 'Контейнеры',
			'link' => HTTP_ROOT . ADMIN . '/structure/containers/',
			);
		$sysinfo = array(
			'title' => 'Системная информация',
			'link' => HTTP_ROOT . ADMIN . '/config/develop/sysinfo/',
			);
		$components = array(
			'title' => 'Компоненты',
			'link' => HTTP_ROOT . ADMIN . '/component/',
			);
			
		$user_data = $this->User->getData();

//		$logout_link = Folder::getUri($this->Settings->getParam('logout_folder_id'));
		$logout_link = HTTP_ROOT . 'user/?logout';
		include DIR_KERNEL . 'Admin/toolbar.tpl';
	}
	
	/**
	 * Отрисовка тулбара.
	 */
	public function __tostring()
	{
		ob_start();
		$this->render();
		return ob_get_clean();
	}
}