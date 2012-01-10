<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс с административными методами.
 * 
 * @version 2011-06-29.0
 */
class Module_GoogleMap_Admin extends Module_GoogleMap implements Admin_ModuleInterface
{
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		$node_params = array(
			/*  'google_key' => array(
				'label' => 'Ключ: (http://code.google.com/intl/ru/apis/maps/signup.html)',
				'type' => 'text',
				'value' => $this->google_key
				), */
			'scale' => array(
				'label' => 'Масштаб:',
				'type' => 'text',
				'value' => $this->scale
				),
			'info_window' => array(
				'label' => 'Содержимое информационного окошка:',
				'type' => 'text',
				'value' => $this->info_window
				),
			'longitude' => array(
				'label' => 'Долгота:',
				'type' => 'text',
				'value' => $this->longitude
				),
			'latitude' => array(
				'label' => 'Широта:',
				'type' => 'text',
				'value' => $this->latitude
				),
		);
		
		return $node_params;
	}

	/**
	 * Вызывается при создании ноды.
	 * 
	 * @return array params
	 */
	public function createNode()
	{
		$params = array(
			/* 'google_key' => '', */
			'info_window' => 'Новая карта',
			'longitude' => '1',
			'latitude' => '1',
			'scale' => '3',
			);
		return $params;
	}

	/**
	 * Получить элементы управления нодой.
	 * 
	 * @return array
	 */
/*	public function getFrontControls()
	{
		$this->default_action = 'edit';

		$items['edit'] = array(
			'popup_window_title' => 'Редактирование',
			'title' => 'Редактировать',
			'link' => $this->Env->current_folder_path . ACTION . '/' . $this->Node->id . '/edit/',
			'ico' => 'edit',
			);
		return $items;
		
	}
*/
}