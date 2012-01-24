<?php
/**
 * Управление пользователями.
 * 
 * @category	System
 * @package		Kernel
 * @subpackage	Module
 * 
 * @uses 		User
 * @uses 		User_Groups
 * 
 * @version		2012-01-24.0
 */
class Admin_Users extends Controller
{
	/**
	 * Constructor.
	 *
	 * @param void
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->View->setTpl('users');
	}

	/**
	 * Action....
	 * 
	 * @param string $uri_path
	 * @return array
	 */
	public function run($uri_path)
	{
		if (isset($_POST['action'])) {
			switch ($_POST['action']) {
				case 'update_user':
					$this->User_Groups->update($_POST['pd']);
					// @todo обновление персональных данных.
					// $this->User->updateAccount($_POST['pd']);
					cmf_redirect(HTTP_ROOT . ADMIN . '/users/list/');
					break;
				default;
			}
		}
		
		$uri_path_parts = explode('/', $uri_path);
		switch ($uri_path_parts[0]) {
			case 'edit':
				if (!is_numeric($uri_path_parts[1])) {
					cmf_redirect(HTTP_ROOT . ADMIN . '/users/list/');
				}
				$this->Breadcrumbs->add('', 'Редактирование пользователя id: ' . $uri_path_parts[1]);
				$this->View->edit_form = $this->getEditFormData($uri_path_parts[1]);
				$this->View->user_data = $this->User->getData($uri_path_parts[1]);
				$this->View->groups_list = $this->User_Groups->getGroupsList();
				
				$elements = array();
				foreach ($this->User_Groups->getGroupsList() as $group_id => $group_value) {
					$value = 0;
					if (array_key_exists($group_id, $this->View->user_data['groups'])) {
						$value = 1;
					}
					$this->View->edit_form['elements']["pd[groups][$group_id]"] = array(
						'label' => $group_value['name'],
						'type' => 'checkbox',
						'value' => $value,
						);
					$elements[] = "pd[groups][$group_id]";
				}
				
				$this->View->edit_form['fieldsets']['user_groups'] = array(
					'title' => 'Группы, в которые включен юзер',
					'elements' => $elements,
					);
				break;
			case '':
			case 'list':
				$this->View->manage = array(
					'groups' => $this->User_Groups->getGroupsList(),
					'users' => $this->User->getList(),
					);
				break;
			case 'group':
				//$this->output_data = $this->getListInFolder($uri_path_parts[1]);
				break;
			case 'create':
				//$this->output_data = $this->getCreateFormData($uri_path_parts[1]);
				break;
			default;
		}
	}
	
	/**
	 * Получить форму редактирования юзера.
	 *
	 * @param
	 * @return
	 */
	public function getEditFormData($user_id = null)
	{
		$data = $this->User->getData($user_id);
		
		return array(
			'hiddens' => array( 
				'action' => 'update_user',
				'pd[user_id]' => $user_id,
				),
			'elements' => array(
				'pd[is_active]' => array(
					'label' => 'Включено',
					'type' => 'checkbox',
					'value' => 1,
					),
				/*'pd[login]' => array(
					'label' => 'Логин',
					'type' => 'string',
					'value' => $row->login,
					),
					*/
				'pd[nickname]' => array(
					'label' => 'Псевдоним',
					'type' => 'string',
					'value' => $data['nickname'],
					),
				'pd[email]' => array(
					'label' => 'email',
					'type' => 'string',
					'value' => $data['email'],
					),
				),
			'autofocus' => 'pd[nickname]',
			'buttons' => array(
				'submit[update_user]' => array(
					'value' => 'Сохранить',
					'type' => 'submit',
					),
				'submit[cancel]' => array(
					'value' => 'Отменить',
					'type' => 'submit',
					'onclick' => 'history.back(); return false;',
					),
				),
			'fieldsets' => array(
				'user_edit' => array(
					'title' => 'Свойства пользователя',
					'elements' => array(
						'pd[is_active]',
						//'pd[login]',
						'pd[nickname]',
						'pd[email]',
						),
					),
				),
			'help' => 'Cправка по добавлению ноды.'
			);
	}
 
}