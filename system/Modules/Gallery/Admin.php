<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс с административными методами.
 * 
 * @version 2011-06-29.0
 */
class Module_Gallery_Admin extends Module_Gallery implements Admin_ModuleInterface
{
	/**
	 * Обработка действий над нодой.
	 * 
	 * @return void
	 */
	public function nodeAction($params)
	{
		// Редактирование записи.
		if (isset($_GET['edit_album']) and is_numeric($_GET['edit_album'])) {
			$this->setTpl('EditAlbum');
			$this->output_data['edit_album_form_data'] = $this->getEditAlbumFormData($_GET['edit_album']);
		}
		// Создание нового альбома.
		elseif (isset($_GET['create_album'])) {
			$this->setTpl('EditAlbum');
			$this->output_data['edit_album_form_data'] = $this->getCreateAlbumFormData();
		}
		// Добавление фотографии.
		elseif (isset($_GET['add_image_to_album']) and is_numeric($_GET['add_image_to_album'])) {
			$this->setTpl('EditImage');
			$this->output_data['edit_image_form_data'] = $this->getCreateImageFormData($_GET['add_image_to_album']);
		}
		// Редактирование фотографии.
		elseif (isset($_GET['edit_image']) and is_numeric($_GET['edit_image'])) {
			$this->setTpl('EditImage');
			$this->output_data['edit_image_form_data'] = $this->getEditImageFormData($_GET['edit_image']);
		}
		
		//$output_data = array();
		//return $output_data;
	}

	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		$node_params = array(
			'media_collection_id' => array(
				'label' => 'media_collection_id:',
				'type' => 'text',
				'value' => $this->media_collection_id,
				),
			'gallery_id' => array(
				'label' => 'gallery_id:',
				'type' => 'text',
				'value' => $this->gallery_id,
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
			'media_collection_id' => '0',
			'gallery_id' => '0',
			);
		return $params;
	}

}