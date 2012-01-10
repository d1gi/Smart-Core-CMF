<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Интерфейс компонента Media
 * 
 * @package Module
 * @version 2011-03-10.0
 */
interface Component_Media_Interface
{
	/**
	 * Добавление файла.
	 * 
	 * @param array $img
	 * @param array $params - параметры для ресайза.
	 * 
	 * @return int|false - ID новой картинки или false.
	 */
	public function createFile($img, $params = false);
	
	/**
	 * Удалить файл.
	 * 
	 * @param int $image_id
	 */
	public function deleteFile($file_id);
	
	public function createStorage();
	
	public function createCollection();
	
	/**
	 * Получить ссылку на файл.
	 * 
	 * @param int $image_id
	 * @return string
	 */
	public function getFileUri($file_id);
	
}
