<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс с административными методами.
 * 
 * @version 2011-06-29.0
 */
class Module_Slider_Admin extends Module_Slider implements Admin_ModuleInterface
{
	/**
	 * Обработка дейсвий над нодой.
	 * 
	 * @return void
	 */
	public function nodeAction($params)
	{
		$this->setTpl('Edit');
		$this->output_data['node_id'] = $this->Node->id;
		$this->output_data['slides'] = $this->getSlides();
		
		if (isset($_GET['delete_img'])) {
			$sql = "SELECT * 
				FROM {$this->DB->prefix()}slider
				WHERE site_id = '{$this->Env->site_id}'
				AND group_id = 1
				AND slider_id = '$_GET[delete_img]'
				";
			$result = $this->DB->query($sql);
			$row = $result->fetchObject();
			unlink(DIR_ROOT . 'images/slider/' . $row->img);	
			
			$sql = " DELETE FROM {$this->DB->prefix()}slider
				WHERE site_id = '{$this->Env->site_id}'
				AND group_id = 1
				AND slider_id = '$_GET[delete_img]'
				";
			$this->DB->exec($sql);
			cf_redirect($_SERVER['HTTP_REFERER']);
		}
		
		$form_data = array(
			'enctype' => 'multipart/form-data',
			'hiddens' => array(
				'node_id' => $this->Node->id,
				),
			'elements' => array(
				'pd[img]' => array(
					'label' => 'Выберите картинку',
					'type' => 'file',
					),
				),
			'buttons' => array(
				'submit[add]' => array(
					'type' => 'submit',
					'value' => 'Добавить картинку',
					),
				),
			);
		$this->output_data['edit_form_data'] = $form_data;
	}

	/**
	 * Получить элементы управления нодой.
	 * 
	 * @return array
	 */
	public function getFrontControls()
	{
		$this->default_action = 'edit';

		$items['edit'] = array(
			'popup_window_title' => 'Управление слайдами',
			'title' => 'Управление слайдами',
			'link' => $this->Env->current_folder_path . ACTION . '/' . $this->Node->id . '/edit/',
			'ico' => 'edit',
			);
		return $items;
	}
	
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		$node_params = array(
			'time_interval' => array(
				'label' => 'Время задержки (мс)',
				'type' => 'string',
				'value' => $this->time_interval,
				),
			);
		return $node_params;
	}

	/**
	 * Вызывается при создании ноды.
	 * 
	 * @return array params
	 * 
	 * @todo мультиязычность.
	 */
	public function createNode()
	{
		$params = array(
			'time_interval' => 10000,
			);
		return $params;
	}
}