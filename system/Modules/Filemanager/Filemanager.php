<?php
/**
 * Module Filemanager.
 * 
 * @uses Component_Media
 * @uses EE
 * 
 * @package Module
 * @version 2011-10-01.0
 */
class Module_Filemanager extends Module
{
	/**
	 * Some value...
	 */
	protected $filemanager_id;
	
	/**
	 * 
	 */
	protected $Media;
	
	protected $thump_width;
	protected $thump_height;
	
	/**
	 * Конструктор.
	 */
	protected function init()
	{
		$this->filemanager_id = $this->Node->params['filemanager_id'];

		$this->thump_width  = 90;
		$this->thump_height = 90;
		
		$media_collection_id = 2; // @todo 
		$this->Media = new Component_Media($media_collection_id);
	}
	
	/**
	 * Запуск модуля.
	 */
	public function run($params)
	{
		$this->EE->addHeadStyle('filemanager.css', HTTP_SYS_RESOURCES . 'styles/filemanager.css');
		
		$form_data = array(
			'enctype' => 'file',
			'hiddens' => array( 
				'node_id' => $this->Node->id,
				),
			'elements' => array(
				'file' => array(
					'label' => 'Файл',
					'type' => 'file',
					'value' => '',
					),
				),
			'buttons' => array(
				'submit[upload]' => array(
					'value' => 'Загрузить',
					'type' => 'submit',
					),
				),
			'help' => 'Cправка по загрузке файла',
			);
		
		$this->View->collections_list = $this->Media->getCollectionsList(true);
		
		$params = array(
			'order' => array(
				'original_filename' => 'asc',
				),
			'thumb_img_size' => array(
				'width' => $this->thump_width,
				'height' => $this->thump_height,
				),
			'paginator' => array(
				'items_per_page' => 0,
				'current_page' => 1,
				),
			);

		if (cf_is_get('sort_name')) {
			switch ($_GET['sort_name']) {
				case 'asc':
					$dir = 'ASC';
					break;
				case 'desc':
					$dir = 'DESC';
					break;
				default;
			}
			$params['order'] = array(
				'original_filename' => $dir,
				);
		}
		
		if (cf_is_get('sort_date')) {
			switch ($_GET['sort_date']) {
				case 'asc':
					$dir = 'ASC';
					break;
				case 'desc':
					$dir = 'DESC';
					break;
				default;
			}
			$params['order'] = array(
				'upload_datetime' => $dir,
				);
		}
		
		if (cf_is_get('sort_size')) {
			switch ($_GET['sort_size']) {
				case 'asc':
					$dir = 'ASC';
					break;
				case 'desc':
					$dir = 'DESC';
					break;
				default;
			}
			$params['order'] = array(
				'size' => $dir,
				);
		}
		
		$this->View->file_list = $this->Media->getFilesList($params);
		$this->View->upload_form = $form_data;
	}	

	/**
	 * Парсер строки запроса.
	 * 
	 * @param string $path - часть URI запроса
	 * @return array|false
	 * 
	 * @todo мультиязычность.
	 */
	public function router($path)
	{
		$path_parts = explode('/', $path);

		// Если запрос пустой, возвращается false.
		if (isset($path_parts[0]) === false ) {
			return false;
		}

		return true;
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
			case 'upload':
				foreach ($_FILES as $key => $value) {
					if ($value['error'] == 0) {
						
						$options = array();
						
						$this->Media->createFile($_FILES[$key], $options);
					}
				}
				break;
			default:
		}
	} 
		
}