<?php
/**
 * Системаная информация.
 * 
 * @author	Artem Ryzhkov
 * @package	Kernel
 * 
 * @uses	EE
 * @uses	Kernel
 * 
 * @version 2011-12-03.1
 */
class Component_Media_Admin extends Component_Media
{
	/**
	 * Управление через панель управления.
	 *
	 * @param string $uri_path - часть ури
	 * @return array
	 */
	public function admin($uri_path)
	{
		// Обработчик POST данных.
		if (isset($_POST['submit']) and !isset($_POST['submit']['cancel'])) {
			
			$submit = false;
			// Вытаскивание ими ключа в переменную $submit.
			foreach ($_POST['submit'] as $key => $value) {
				$submit = $key;
			}

			switch ($submit) {
				case 'update_collection':
					$this->updateCollection($_POST['collection_id'], $_POST['pd']);
					break;
				default;
			}
		}
		
		/*
		if (isset($_GET['del_item']) and is_numeric($_GET['del_item'])) {
			$this->deleteText($_GET['del_item']);
			cf_redirect(HTTP_ROOT . ADMIN . '/module/Texter/');
		}
		*/
		$this->setTpl('Admin');
		$data = array();

		$uri_path_parts = explode('/', $uri_path);
		
		if (isset($uri_path_parts[0]) and !empty($uri_path_parts[0]) and is_numeric($uri_path_parts[1])) {
			switch ($uri_path_parts[0]) {
				case 'collection':
					$breadcrumb_title = 'Редактирование коллекции: ' . $uri_path_parts[1];
					$this->setTpl('AdminCollection');
					
					$data['edit_collection_form_data'] = $this->getEditCollectionFormData($uri_path_parts[1]);
					
					break;
				case 'storage':
					$breadcrumb_title = '@todo Хранилище: ' . $uri_path_parts[1];
					break;
				default;
			}

			$this->EE->addBreadCrumb($uri_path_parts[0] . '/', $breadcrumb_title);
		}
		// Главная страничка управления.
		else {
			$data['collections'] = $this->getCollectionsList();
			$data['storages']	 = $this->getStoragesList();
			$data['create_collection_form_data'] = $this->getCreateCollectionFormData();
			$data['create_storage_form_data'] = $this->getCreateStorageFormData();
		}
		
		return $data;
	}
	
	/**
	 * Создание хранилища.
	 */
	public function createStorage() // @todo 
	{
	
		return;
	}
	
	/**
	 * Создание коллекции.
	 */
	public function createCollection() // @todo 
	{
	
		return;
	}
	/**
	 * Обновление коллекции.
	 *
	 * @param int $collection_id
	 * @param array $pd
	 * @return bool
	 */
	public function updateCollection($collection_id, $pd)
	{
		$params	= $this->DB->quote(serialize($pd['params']));
		$name	= $this->DB->quote(trim($pd['name']));
		$title	= $this->DB->quote(trim($pd['title']));
		$relative_path		= $this->DB->quote(trim($pd['relative_path']));
		$default_storage_id	= $this->DB->quote(trim($pd['default_storage_id']));
		
		$sql = "
			UPDATE {$this->DB->prefix()}media_collections SET
				name = $name,
				title = $title,
				default_storage_id = $default_storage_id,
				relative_path = $relative_path,
				params = $params
			WHERE site_id = '{$this->Env->site_id}'
			AND collection_id = '$collection_id' ";
		$this->DB->query($sql);
		return true;
	}
	
	/**
	 * Данные формы редактирования коллекции.
	 *
	 * @param int $collection_id
	 * @return array
	 */
	public function getEditCollectionFormData($collection_id)
	{
		$collection = $this->getCollectionData($collection_id);

		return array(
			'action' => HTTP_ROOT . ADMIN . '/component/Media/collection/',
			'hiddens' => array(
				'collection_id' => $collection_id,
				),
			'elements' => array(
				'pd[name]' => array(
					'label' => 'Техническое имя',
					'type' => 'string',
					'value' => $collection['name'],
					),
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'string',
					'value' => $collection['title'],
					),
				'pd[relative_path]' => array(
					'label' => 'Относительный путь',
					'type' => 'string',
					'value' => $collection['relative_path'],
					),
				'pd[descr]' => array(
					'label' => 'Описание',
					'type' => 'textarea',
					'value' => $collection['descr'],
					),
				'pd[default_storage_id]' => array(
					'label' => 'Хранилище по умолч.',
					'type' => 'select',
					'options' => $this->getStoragesList(),
					'value' => $collection['default_storage_id'],
					),
				'pd[params][use_type_prefix]' => array(
					'label' => 'Использовать префикс типа в относительном пути файла при загрузке',
					'type' => 'checkbox',
					'value' => $collection['params']['use_type_prefix'],
					),
				'pd[params][compress_original_img]' => array(
					'label' => 'Сжимать оригинальные картинки',
					'type' => 'checkbox',
					'value' => $collection['params']['compress_original_img'],
					),
				'pd[params][file_relative_path]' => array(
					'label' => 'Отн. путь файла',
					'type' => 'string',
					'value' => $collection['params']['file_relative_path'],
					),
				'pd[params][file_mask]' => array(
					'label' => 'Маска имени файла',
					'type' => 'string',
					'value' => $collection['params']['file_mask'],
					),
				'pd[params][allow_types]' => array(
					'label' => 'Типы файлов разрешенные к загрузке',
					'type' => 'string',
					'value' => $collection['params']['allow_types'],
					),
				'pd[params][original_resize_width]' => array(
					'label' => 'Ширина для сжатия оригинального файла',
					'type' => 'string',
					'value' => $collection['params']['original_resize_width'],
					),
				'pd[params][original_resize_height]' => array(
					'label' => 'Высота для сжатия оригинального файла',
					'type' => 'string',
					'value' => $collection['params']['original_resize_height'],
					),
				'pd[params][original_resize_quality]' => array(
					'label' => 'Качество для сжатия оригинального файла',
					'type' => 'string',
					'value' => $collection['params']['original_resize_quality'],
					),
				'pd[params][original_resize_fit]' => array(
					'label' => 'Подгонка по размеру',
					'type' => 'select',
					'options' => array(
						'inside' => 'по минимальной строне с сохранением пропорций.',
						'outside' => 'по максимальной стороне с сохранением пропорций.',
						'fill' => 'заполнить исходной картинкой новую без сохранения пропорций.',
						),
					'value' => $collection['params']['original_resize_fit'],
					),
				'pd[params][original_resize_scale]' => array(
					'label' => 'Масштабирование',
					'type' => 'select',
					'options' => array(
						'down' => 'только уменьшение',
						'up' => 'только увеличение',
						'any' => 'в любом случае',
						),
					'value' => $collection['params']['original_resize_scale'],
					),
				'pd[params][original_convert_to]' => array(
					'label' => 'Конвертировать в',
					'type' => 'string',
					'value' => $collection['params']['original_convert_to'],
					),
				),
			'buttons' => array(
				'submit[update_collection]' => array(
					'type' => 'submit',
					'value' => 'Сохранить изменения',
					),
				'submit[cancel]' => array(
					'type' => 'submit',
					'value' => 'Отменить',
					),
				),
			'fieldsets' => array(
				'base' => array(
					'title' => 'Основные свойства коллекции',
					'elements' => array(
						'pd[name]',
						'pd[title]',
						'pd[relative_path]',
						'pd[descr]',
						'pd[default_storage_id]',
						),
					),
				'properties' => array(
					'title' => 'Параметры',
					'elements' => 'all',
					),
				),
			);
	}
	
	/**
	 * Данные формы создания коллекции.
	 *
	 * @return array
	 */
	public function getCreateCollectionFormData()
	{
		return array(
			'action' => HTTP_ROOT . ADMIN . '/component/Media/',
			'hiddens' => array(
				'action' => 'create_collection',
				),
			'elements' => array(
				'pd[name]' => array(
					'label' => 'Техническое имя',
					'type' => 'string',
					'value' => '',
					),
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'string',
					'value' => '',
					),
				'pd[relative_path]' => array(
					'label' => 'Относительный путь',
					'type' => 'string',
					'value' => '',
					),
				'pd[default_storage_id]' => array(
					'label' => 'Хранилище',
					'type' => 'select',
					'options' => $this->getStoragesList(),
					'value' => '',
					),
				),
			'buttons' => array(
				'submit[create_collection]' => array(
					'type' => 'submit',
					'value' => '@todo Создать коллекцию',
					'disabled' => true,
					),
				),
			);
	}
	
	/**
	 * Данные формы создания хранилища.
	 *
	 * @return array
	 */
	public function getCreateStorageFormData()
	{
		return array(
			'action' => HTTP_ROOT . ADMIN . '/component/Media/',
			'hiddens' => array(
				'action' => 'create_storage',
				),
			'elements' => array(
				'pd[name]' => array(
					'label' => 'Техническое имя',
					'type' => 'string',
					'value' => '',
					),
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'string',
					'value' => '',
					),
				'pd[path]' => array(
					'label' => 'Путь',
					'type' => 'string',
					'value' => '',
					),
				),
			'buttons' => array(
				'submit[create_storage]' => array(
					'type' => 'submit',
					'value' => '@todo Создать хранилище',
					'disabled' => true,
					),
				),
			);
	}
	
	/**
	 * Получить список всех хранилищ.
	 *
	 * @return array
	 */
	public function getStoragesList()
	{
		$data = array();
		$sql = "SELECT * FROM {$this->DB->prefix()}media_storages WHERE site_id = '{$this->Env->site_id}' ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$data[$row->storage_id] = array(
				'name'	=> $row->name,
				'path'	=> $row->path,
				'title'	=> $row->title,
				'descr'	=> $row->descr,
				);
		}
		return $data;
	}
	
}
