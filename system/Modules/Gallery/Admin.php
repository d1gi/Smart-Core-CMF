<?php 
/**
 * Класс с административными методами.
 * 
 * @version 2012-01-14.0
 */
class Module_Gallery_Admin extends Module_Gallery implements Admin_ModuleInterface
{
	/**
	 * Обработка действий над нодой.
	 * 
	 */
	public function nodeAction($params)
	{
		// Редактирование записи.
		if (isset($_GET['edit_album']) and is_numeric($_GET['edit_album'])) {
			$this->View->setTpl('EditAlbum');
			$this->View->edit_album_form_data = $this->getEditAlbumFormData($_GET['edit_album']);
		}
		// Создание нового альбома.
		elseif (isset($_GET['create_album'])) {
			$this->View->setTpl('EditAlbum');
			$this->View->edit_album_form_data = $this->getCreateAlbumFormData();
		}
		// Добавление фотографии.
		elseif (isset($_GET['add_image_to_album']) and is_numeric($_GET['add_image_to_album'])) {
			$this->View->setTpl('EditImage');
			$this->View->edit_image_form_data = $this->getCreateImageFormData($_GET['add_image_to_album']);
		}
		// Редактирование фотографии.
		elseif (isset($_GET['edit_image']) and is_numeric($_GET['edit_image'])) {
			$this->View->setTpl('EditImage');
			$this->View->edit_image_form_data = $this->getEditImageFormData($_GET['edit_image']);
		}
	}

	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		return array(
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
	}
}