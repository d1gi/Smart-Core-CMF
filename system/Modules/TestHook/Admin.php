<?php 
/**
 * Класс с административными методами.
 * 
 * @version 2011-06-29.0
 */
class Module_TestHook_Admin extends Module_TestHook implements Admin_ModuleInterface
{
	/**
	 * Обработка действий над нодой.
	 * 
	 * @return void
	 */
/*	public function nodeAction($params)
	{
		$this->setTpl('Edit');

		$form_data = array(
			'action' => $this->Env->current_folder_path,
			'target' => '_parent',
			'hiddens' => array(
				'node_id' => $this->Node->id,
				),
			'elements' => array(
				'pd[text]' => array(
					'type' => 'textarea',
					'value' => $this->val,
					),
				),
			'buttons' => array(
				'submit[save]' => array(
					'type' => 'submit',
					'value' => 'Сохранить изменения',
					),
				'submit[cancel]' => array(
					'type' => 'submit',
					'value' => 'Отменить',
					),
				),
			);
		$this->output_data['edit_form_data'] = $form_data;
	}
*/
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array $node_params
	 */
/*	public function getParams()
	{
		$node_params = array(
			'val' => array(
				'label' => 'val:',
				'type' => 'text',
				'value' => $this->val,
				),
			'val2' => array(
				'label' => 'val2:',
				'type' => 'text',
				'value' => $this->val2,
				),
			);
		return $node_params;
	}
*/
	/**
	 * Вызывается при создании ноды.
	 * 
	 * @return array $params
	 */
/*	public function createNode()
	{
		$params = array(
			'val' => '0',
			'val2' => '0',
			);
		return $params;
	}
*/	

	/**
	 * Получить элементы управления нодой.
	 * 
	 * @return array
	 */
/*	public function getFrontControls()
	{
		$this->default_action = 'edit';

		$items = array(
			'edit' => array(
				'popup_window_title' => 'Редактирование текстового блока',
				'title' => 'Редактировать',
				'link' => $this->Env->current_folder_path . ACTION . '/' . $this->Node->id . '/edit/',
				'ico' => 'edit',
				),
			'history' => array(
				'popup_window_title' => 'История изменений',
				'title' => 'История',
				'link' => $this->Env->current_folder_path . ACTION . '/' . $this->Node->id . '/history/',
				'ico' => 'edit',
				),
			);
		return $items;
		
	}
*/	
	/**
	 * Внутренние элменты управления ноды.
	 * 
	 * @access public
	 * @returns array|false
	 */
/*	public function getFrontControlsInner()
	{
		return $this->frontend_inner_controls;
	}
*/	
	/**
	 * Действие по умолчанию.
	 * 
	 * @access public
	 * @returns string|false
	 */
/*	public function getFrontControlsDefaultAction()
	{
		return $this->default_action;
	}
*/
}
