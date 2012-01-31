<?php
/**
 * Модуль Фотогалерея.
 * 
 * @package Module
 * @version 2011-10-01.0
 */
class Module_Gallery extends Module
{
	/**
	 * Экземпляр галереи.
	 * @var int
	 */
	protected $gallery_id;
	
	/**
	 * ИД медиа коллекции.
	 * @var int
	 */
	protected $media_collection_id;
	
	/**
	 * Параметры миниатюр.
	 * @var array
	 */
	private $thumbnail_params;
	
	/**
	 * Объект медиа коллекции.
	 * @var object
	 */
	private $Media;
	
	/**
	 * Конструктор.
	 */
	protected function init()
	{
		$this->setVersion(0.1);
		
		$this->Node->setDefaultParams(array(
			'gallery_id'			=> 0,
			'media_collection_id'	=> 0,
			));
		
		$sql = "SELECT thumbnail_width AS width, thumbnail_height AS height
			FROM {$this->DB->prefix()}galleries
			WHERE site_id = '{$this->Env->site_id}'
			AND gallery_id = '{$this->Node->getParam('gallery_id')}' ";
		$this->thumbnail_params = $this->DB->getRow($sql);
		
		$this->Media = new Component_Media($this->Node->getParam('media_collection_id'));
	}
	
	/**
	 * Запуск модуля.
	 */
	public function run($params)
	{
		$this->EE->useScriptLib('lightview');
		
		// Просмотр альбома.
		if (isset($_GET['album']) and is_numeric($_GET['album'])) {
			$album_id = $_GET['album'];
			$this->View->setTpl('Album');
			
			$front_controls = array();
			$front_controls['add_image'] = array(
				'popup_window_title' => 'Добавить фотографию',
				'title' => 'Добавить фотографию',
				'link' => $this->Env->current_folder_path . ACTION . '/' . $this->Node->id . '/?add_image_to_album=' . $album_id,
				'ico' => 'new',
				);
			$this->frontend_controls = $front_controls;
			
			$sql = "SELECT descr, title
				FROM {$this->DB->prefix()}galleries_albums
				WHERE site_id = '{$this->Env->site_id}'
				AND gallery_id = {$this->gallery_id}
				AND album_id = '$album_id' ";
			$this->View->album = $this->DB->getRow($sql);
			$this->Breadcrumbs->add('', $this->View->album['title'], $this->View->album['descr']);
			
			$images = array();
			$images_count = 0;
			$sql = "SELECT * 
				FROM {$this->DB->prefix()}galleries_images
				WHERE site_id = '{$this->Env->site_id}'
				AND gallery_id = {$this->gallery_id}
				AND album_id = '$album_id' ";
			$result = $this->DB->query($sql);
			while ($row = $result->fetchObject()) {
				$images_count++;
				$images[$row->image_id] = array(
					'descr' => $row->descr,
					'create_datetime' => $row->create_datetime,
					'thumbnail_link' => $this->Media->getFileUri($row->image_id, $this->thumbnail_params),
					'original_link' => $this->Media->getFileUri($row->image_id),
					);
				$frontend_inner_control['edit'] = array(
					'popup_window_title' => 'Редактировать фотографию',
					'title' => 'Редактировать',
					'link' => $this->Env->current_folder_path . ACTION . '/' . $this->Node->id . '/?edit_image=' . $row->image_id,
					'ico' => 'edit',
					);
				$this->frontend_inner_controls['news_item_id_' . $row->image_id] = $frontend_inner_control;
					
			}			
			$this->View->album['images_count'] = $images_count;
			$this->View->images = $images;
			return; 
		}
		
		// Просмотр списка альбомов.
		$albums = array();
		$albums_count = 0;
		
		$front_controls = array();
		$front_controls['create_album'] = array(
			'popup_window_title' => 'Создать альбом',
			'title' => 'Создать альбом',
			'link' => $this->Env->current_folder_path . ACTION . '/' . $this->Node->id . '/?create_album',
			'ico' => 'new',
			);
		$this->frontend_controls = $front_controls;
		
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}galleries_albums
			WHERE site_id = '{$this->Env->site_id}'
			AND gallery_id = {$this->gallery_id}
			ORDER BY pos ";
		$result = $this->DB->query($sql);
		
		$Date = new Helper_Date();
		
		while ($row = $result->fetchObject()) {
			$albums_count++;
			$sql2 = "SELECT count(image_id) AS cnt
				FROM {$this->DB->prefix()}galleries_images   
				WHERE site_id = '{$this->Env->site_id}'
				AND gallery_id = {$this->gallery_id}
				AND album_id = {$row->album_id}
				";
			$result2 = $this->DB->query($sql2);
			$row2 = $result2->fetchObject();
			
			$last_update_datetime = $Date->getTimestamp($row->last_update_datetime);
			$create_datetime = $Date->getTimestamp($row->create_datetime);
			$count = $row2->cnt;
			
			$albums[$row->album_id] = array(
				'uri_part' => $row->uri_part,
				'title' => $row->title,
				'descr' => $row->descr,
				'count' => $count,
				'thumbnail_link' => $this->Media->getFileUri($row->thumbnail_image_id, $this->thumbnail_params),
				'create_datetime' => $create_datetime,
				'last_update_datetime' => $last_update_datetime,
				);
			$frontend_inner_control['edit'] = array(
				'popup_window_title' => 'Редактировать альбом',
				'title' => 'Редактировать',
				'link' => $this->Env->current_folder_path . ACTION . '/' . $this->Node->id . '/?edit_album=' . $row->album_id,
				'ico' => 'edit',
				);
			$this->frontend_inner_controls['news_item_id_' . $row->album_id] = $frontend_inner_control;
		}
	
		$this->View->albums = $albums;
		$this->View->albums_count = $albums_count;
	}	
	 
	/**
	 * Получить форму редактирования изображения.
	 *
	 * @param
	 * @return
	 */
	public function getEditImageFormData($image_id)
	{
		$albums = array();
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}galleries_albums
			WHERE site_id = '{$this->Env->site_id}'
			AND gallery_id = {$this->gallery_id}
			";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$albums[$row->album_id] = $row->title;
		}
		
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}galleries_images
			WHERE site_id = '{$this->Env->site_id}'
			AND gallery_id = {$this->gallery_id}
			AND image_id = '$image_id'
			";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		
		return array(
			'action' => $this->Env->current_folder_path,
			'target' => '_parent',
			'enctype' => 'multipart/form-data',
			'hiddens' => array(
				'node_id' => $this->Node->id,
				'pd[image_id]' => $image_id,
				),
			'elements' => array(
				'image' => array(
					'label' => 'Фото',
					'type' => 'html',
					'value' => '<img src="' . $this->Media->getFileUri($image_id, $this->thumbnail_params) . '" alt=""/>',
					),
				'pd[album_id]' => array(
					'label' => 'Альбом',
					'type' => 'select',
					'value' => $row->album_id,
					'options' => $albums,
					),
				'pd[descr]' => array(
					'label' => 'Описание',
					'type' => 'textarea',
					'value' => $row->descr,
					'style' => 'width: 100%;',
					),
				),				
			'buttons' => array(
				'submit[update_image]' => array(
					'type' => 'submit',
					'value' => 'Сохранить фотографию',
					),
				'submit[delete_image]' => array(
					'type' => 'submit',
					'value' => 'Удалить',
					'onclick' => "return confirm('Вы уверены, что хотите удалить фотографию?')",
					),
				'submit[cancel_image]' => array(
					'type' => 'submit',
					'value' => 'Отменить',
					),
				),
			);
	}
	
	/**
	 * Получить форму добавления фотографии.
	 *
	 * @param int $album_id
	 * @return array 
	 */
	public function getCreateImageFormData($album_id)
	{
		return array(
			'action' => $this->Env->current_folder_path,
			'target' => '_parent',
			'enctype' => 'multipart/form-data',
			'hiddens' => array(
				'node_id' => $this->Node->id,
				'pd[album_id]' => $album_id,
				),
			'elements' => array(
				'image' => array(
					'label' => 'Фото',
					'type' => 'file',
					),
				'pd[descr]' => array(
					'label' => 'Описание',
					'type' => 'textarea',
					'value' => '',
					'style' => 'width: 100%;',
					),
				),				
			'buttons' => array(
				'submit[create_image]' => array(
					'type' => 'submit',
					'value' => 'Добавить фотографию',
					),
				'submit[cancel]' => array(
					'type' => 'submit',
					'value' => 'Отменить',
					),
				),
			);
	}
	
	/**
	 * Получить форму создания альбома.
	 *
	 * @return array
	 */
	public function getCreateAlbumFormData()
	{
		$sql = "SELECT max(pos) AS max_pos
			FROM {$this->DB->prefix()}galleries_albums
			WHERE site_id = '{$this->Env->site_id}'
			AND gallery_id = {$this->gallery_id}
			";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		
		return array(
			'action' => $this->Env->current_folder_path,
			'target' => '_parent',
			'enctype' => 'multipart/form-data',
			'hiddens' => array(
				'node_id' => $this->Node->id,
				),
			'elements' => array(
				'pd[uri_part]' => array(
					'label' => 'Часть адреса',
					'type' => 'string',
					'value' => '',
					),
				'pd[title]' => array(
					'label' => 'Загологок',
					'type' => 'string',
					'value' => '',
					),
				'pd[pos]' => array(
					'label' => 'Позиция',
					'type' => 'string',
					'value' => $row->max_pos + 1,
					),
				'pd[descr]' => array(
					'label' => 'Описание',
					'type' => 'textarea',
					'value' => '',
					'style' => 'width: 100%;',
					),
				),				
			'buttons' => array(
				'submit[create_album]' => array(
					'type' => 'submit',
					'value' => 'Создать альбом',
					),
				'submit[cancel]' => array(
					'type' => 'submit',
					'value' => 'Отменить',
					),
				),
			);
	}
	
	/**
	 * Получить форму редактирования альбома.
	 *
	 * @param int $album_id
	 * @return array
	 */
	public function getEditAlbumFormData($album_id)
	{
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}galleries_albums
			WHERE site_id = '{$this->Env->site_id}'
			AND gallery_id = {$this->gallery_id}
			AND album_id = '$album_id'
			";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		
		return array(
			'action' => $this->Env->current_folder_path,
			'target' => '_parent',
			'enctype' => 'multipart/form-data',
			'hiddens' => array(
				'node_id' => $this->Node->id,
				'pd[album_id]' => $album_id,
				),
			'elements' => array(
				'pd[uri_part]' => array(
					'label' => 'Часть адреса',
					'type' => 'string',
					'value' => $row->uri_part,
					),
				'pd[title]' => array(
					'label' => 'Загологок',
					'type' => 'string',
					'value' => $row->title,
					),
				'pd[pos]' => array(
					'label' => 'Позиция',
					'type' => 'string',
					'value' => $row->pos,
					),
				'pd[descr]' => array(
					'label' => 'Описание',
					'type' => 'textarea',
					'value' => $row->descr,
					'style' => 'width: 100%;',
					),
				),				
			'buttons' => array(
				'submit[update_album]' => array(
					'type' => 'submit',
					'value' => 'Сохранить изменения',
					),
				'submit[delete_album]' => array(
					'type' => 'submit',
					'value' => 'Удалить',
					'onclick' => "return confirm('Вы уверены, что хотите удалить альбом?')",
					'disabled' => true,
					),
				'submit[cancel]' => array(
					'type' => 'submit',
					'value' => 'Отменить',
					),
				),
			);
	}
	
	/**
	 * Обновление альбома.
	 *
	 * @param array $pd
	 * @return 
	 */
	public function updateAlbum($pd)
	{
		$uri_part	= $this->DB->quote(trim($pd['uri_part']));
		$title		= $this->DB->quote(trim($pd['title']));
		$descr		= $this->DB->quote(trim($pd['descr']));
		if (is_numeric($pd['pos'])) {
			$pos = $pd['pos'];
		} else {
			$pos = 0;
		}
		
		$sql = "
			UPDATE {$this->DB->prefix()}galleries_albums SET
				title = $title,
				descr = $descr,
				uri_part = $uri_part,
				pos = '$pos'
			WHERE site_id = '{$this->Env->site_id}'
			AND album_id = '$pd[album_id]'
			AND gallery_id = {$this->gallery_id}
			";
		$this->DB->exec($sql);
	}
	
	/**
	 * Создание альбома.
	 *
	 * @param array - данные альбома:
	 * 			- title
	 * 			- descr
	 * 			- uri_part
	 * 			- pos
	 * @return int - ID созданного альбома
	 */
	public function createAlbum($pd)
	{
		$title = $this->DB->quote(trim($pd['title']));
		$descr = $this->DB->quote(trim($pd['descr']));
		$uri_part = $this->DB->quote($pd['uri_part']);
		if (is_numeric($pd['pos'])) {
			$pos = $pd['pos'];
		} else {
			$pos = 0;
		}
		$sql = "
			INSERT INTO {$this->DB->prefix()}galleries_albums
				(site_id, gallery_id, uri_part, title, descr, pos, create_datetime, last_update_datetime )
			VALUES
				('{$this->Env->site_id}', '{$this->gallery_id}', $uri_part, $title, $descr, '$pos', NOW(), NOW() )
			";
		$this->DB->query($sql);
		return $this->DB->lastInsertId();
	}
	
	/**
	 * Добавление фотографии.
	 *
	 * @param array $pd
	 * @return
	 */
	public function createImage($pd)
	{
		$album_id = $pd['album_id'];
		$descr = $this->DB->quote($pd['descr']);
		
		$image_id = $this->Media->createFile($_FILES['image'], $this->thumbnail_params);
		
		if ($image_id > 0) {
			$sql = "
				INSERT INTO {$this->DB->prefix()}galleries_images
					(site_id, album_id, gallery_id, image_id, descr, create_datetime )
				VALUES
					('{$this->Env->site_id}', '$album_id', '{$this->gallery_id}', '$image_id', $descr,  NOW() )
				";
			$this->DB->query($sql);

			$sql = "
				UPDATE {$this->DB->prefix()}galleries_albums SET
					thumbnail_image_id = '$image_id',
					last_update_datetime = NOW()
				WHERE site_id = '{$this->Env->site_id}'
				AND album_id = '$album_id'
				AND gallery_id = {$this->gallery_id}
				";
			$this->DB->query($sql);
		}
		// @todo ненадо тут редиректиться!
		cmf_redirect("?album=$album_id");
	}
	
	/**
	 * Обновление картинки.
	 *
	 * @param array $pd
	 * @return
	 */
	public function uptadeImage($pd)
	{
		$album_id = $pd['album_id'];
		$image_id = $pd['image_id'];
		$descr = $this->DB->quote($pd['descr']);
		
		$sql = "
			UPDATE {$this->DB->prefix()}galleries_images SET
				album_id = '$album_id',
				descr = $descr
			WHERE site_id = '{$this->Env->site_id}'
			AND image_id = '$image_id'
			AND gallery_id = {$this->gallery_id}
			";
		$this->DB->query($sql);
		
		$sql = "
			UPDATE {$this->DB->prefix()}galleries_albums SET
				last_update_datetime = NOW()
			WHERE site_id = '{$this->Env->site_id}'
			AND album_id = '$album_id'
			AND gallery_id = {$this->gallery_id}
			";
		$this->DB->query($sql);
		// @todo ненадо тут редиректиться!
		cmf_redirect("?album=$album_id#image_$image_id");
	}
	
	/**
	 * Удаление картинки
	 *
	 * @param array $pd
	 * @return
	 */
	public function deleteImage($pd)
	{
		$album_id = $pd['album_id'];
		$image_id = $pd['image_id'];
		
		$this->Media->deleteFile($image_id, $this->thumbnail_params);
		
		$sql = " DELETE FROM {$this->DB->prefix()}galleries_images
			WHERE site_id = '{$this->Env->site_id}'
			AND gallery_id = {$this->gallery_id}
			AND image_id = '$image_id'
			";
		$this->DB->exec($sql);
		
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}galleries_albums
			WHERE site_id = '{$this->Env->site_id}'
			AND gallery_id = {$this->gallery_id}
			AND album_id = '$album_id'
			";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
			
		// Переопределение миниатюры по умолчанию.
		if ($row->thumbnail_image_id == $image_id) {
			$sql = "SELECT max(image_id) AS max_image_id
				FROM {$this->DB->prefix()}galleries_images
				WHERE site_id = '{$this->Env->site_id}'
				AND gallery_id = {$this->gallery_id}
				AND album_id = '$album_id'
				";
			$result = $this->DB->query($sql);
			$row = $result->fetchObject();
			$max_image_id = $row->max_image_id;
			
			$sql = "
				UPDATE {$this->DB->prefix()}galleries_albums SET
					thumbnail_image_id = '$max_image_id'
				WHERE site_id = '{$this->Env->site_id}'
				AND gallery_id = {$this->gallery_id}
				AND album_id = '$album_id'
				";
			$this->DB->query($sql);
		}
		
		// @todo ненадо тут редиректиться!
		cmf_redirect("?album=$album_id");
	}
	
	/**
	 * Обработчик POST данных
	 * 
	 * @param int $pd
	 * @param string $submit
	 * @return void
	 */
	public function postProcessor($pd, $submit)
	{
		switch ($submit) {
			case 'update_album':
				$this->updateAlbum($pd);
				break;
			case 'create_album':
				$this->createAlbum($pd);
				break;
			case 'create_image':
				$this->createImage($pd);
				break;
			case 'update_image':
				$this->uptadeImage($pd);
				break;
			case 'delete_image':
				$this->deleteImage($pd);
				break;
			case 'cancel_image':
				cmf_redirect('?album=' . $pd['album_id'] . '#image_' . $pd['image_id'] );
				break;
			default:
		}
	}
	
}