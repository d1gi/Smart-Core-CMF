<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Компонент: Медиа.
 * 
 * @uses DB
 * 
 * @version 2011-12-03.1
 */
class Component_Media extends Controller // implements Component_Media_Interface
{
	/**
	 * ID коллекции.
	 * @var int
	 */
	protected $collection_id;
	
	/**
	 * Полный путь коллекции в текущем хранилище.
	 * @var int
	 */
	protected $path;
	
	/**
	 * Относительный путь коллекии в хранилище.
	 * @var string
	 */
	protected $collection_path;
	
	/**
	 * Хранилище коллеции по умолчанию.
	 * @var string
	 */
	protected $default_storage_id;
	
	/**
	 * Типы файлов.
	 * @var array
	 */
	protected $types;
	
	/**
	 * Параметры.
	 * @var array
	 */
	protected $params;
	
	/**
	 * Сообщения об ошибках
	 * @var array
	 */
	private $error;
	
	/**
	 * Конструктор.
	 * 
	 * @param int $collection_id
	 * @param resource $db_connection
	 */
	public function __construct($collection_id = false, $db_connection = false)
	{
		parent::__construct();
		
		$this->error = 0;
		
		if ($db_connection !== false) {
			$this->DB = $db_connection;
		}
		
		// Группы файлов по расширениям.
		$this->types = array(
			'img' => 'jpg, jpeg, gif, png, bmp, tiff, ico',
			'executable' => 'bat, bin, cmd, csh, com, exe, sh',
			'doc' => 'djv, djvu, doc, docx, odt, pdf, txt, rtf',
			'spreadsheet' => 'ods, xls, xslx',
			'font' => 'fnt, fon, otf, ttf',
			'audio' => 'ape, flac, mp3, mid, ogg, wma, wav',
			'video' => '3gp, avi, flv, mpg, mpe, mpeg, mpeg4, mp4, mov, mkv, ogv, rm, qt, qtm',
			'archive' => '7z, bzip, bz2, bzip2, gz, gzip, rar, tar, tgz, tz, xz, zip',
			'web' => 'css, js, json, jsp, htaccess, html, htm, php, php5, rss, tpl, url, web, xss, xht, xhtm, xhtml',
			'settings' => 'cfg, ini, inf, isp, opt, opts, set, settings',
			);

		// Параметры коллекции по умолчанию.
		$this->params = array(
			'use_type_prefix' => true,				// Использовать префикс типа в относительном пути файла. 
			'compress_original_img' => true,		// Сжимать оригинальные картинки.
			'file_relative_path' => '%Y/%m/%d/',	// Маска относительного пути файла.
			'file_mask' => '%H_%i_%RAND(10)',		// Маска имени файла. Если пустая строка,то использовать оригинальное имя файла, совместимое с вебформатом т.е. без пробелов и русских букв.
			'allow_types' => 'all',					// Список типов файлов разрешенных к загрузке.
			'original_resize_width' => 1600,		// Ширина для сжатия оригинального файла.
			'original_resize_height' => 1200,		// Высота для сжатия оригинального файла.
			'original_resize_quality' => 80,		// Качество для сжатия оригинального файла.
			'original_resize_fit' => 'inside',		// Подгонка по размеру.
			'original_resize_scale' => 'down',		// Масштабирование.
			'original_convert_to' => false,			// Форсированно конвертировать оригинальную картинку в заданный формат.
			);
		
		$this->collection_id = $collection_id;

		$sql = "SELECT 
				s.path AS storage_path,
				c.relative_path AS collection_path,
				c.default_storage_id AS default_storage_id,
				c.relative_path AS collection_path,
				c.params AS collection_params
			FROM {$this->DB->prefix()}media_collections AS c,
				 {$this->DB->prefix()}media_storages AS s
			WHERE c.site_id = '{$this->Env->site_id}'
			AND c.collection_id = '{$collection_id}'
			AND s.storage_id = c.default_storage_id ";
		$result = $this->DB->query($sql);
		
		if ($result->rowCount()) {
			$row = $result->fetchObject();
			$this->default_storage_id	= $row->default_storage_id;
			$this->collection_path		= $row->collection_path;
			
			// @todo Сделать гибко!!! а то сейчас только с локалки работает :(
			$this->path = DIR_ROOT . $row->storage_path . $row->collection_path;
		} else {
			$this->path = false;
		}
		
		// Если у коллекции есть параметры, то устанавливить их.
		if (!empty($row->collection_params)) {
			foreach (unserialize($row->collection_params) as $key => $value) {
				$this->params[$key] = $value;
			}
		}
	}
	
	/**
	 * Добавление файла.
	 * 
	 * @param array $file - $_FILE[key]
	 * @param array $options - параметры для ресайза. Формат массива идентичен $this->params и может быть не полным.
	 * @return int|false - ID новой картинки или false.
	 * 
	 * @todo возврат false в случе неудачи.
	 */
	public function createFile(array $file, $options = null)
	{
		if ($file['error'] != 0) {
			return false;
		}
		
		// Установка параметров.
		$params = $this->params;
		if (is_array($options)) {
			foreach ($options as $key => $value) {
				$params[$key] = $value;
			}
		}
		
		// Получение типа файла.
		$type = $this->getFileType($file['name']);

		// Использовать префикс типа в относительном пути файла. 
		$type_prefix = $params['use_type_prefix'] == 0 ? '' : '/';
		
		// Обработка маски относительного пути.
		$relative_path = $params['file_relative_path'];
		$relative_path = str_replace('%Y', date('Y'), $relative_path);
		$relative_path = str_replace('%m', date('m'), $relative_path);
		$relative_path = str_replace('%d', date('d'), $relative_path);

		// Получить расширение файла.
		$ext = strtolower(substr($file['name'], strrpos($file['name'], '.') + 1));

		// формирование имени файла (без расширения).
		if (empty($params['file_mask'])) {
			$Helper_Uri = new Helper_Uri();
			// @todo проверка на:
			//		- запрещенные расширения, типа php, php5, html
			//		- существование файла с такимже именем
			$filename = basename($Helper_Uri->preparePart($file['name']), '.' . $ext);
		} else {
			$filename = $params['file_mask'];
		}
		
		// Обработка маски имени файла.
		// @todo сделать выражение %RAND(10) на регулярках.
		$filename = str_replace('%H', date('H'), $filename);
		$filename = str_replace('%i', date('i'), $filename);
		$filename = str_replace('%RAND(10)', substr(md5(microtime(true) . $file['name']), 0, 10), $filename);
		
		// Формируется полный путь к файлу в ФС ОС :)
		$path = $this->path . $type_prefix . $relative_path;
		
		// Создать папку, если она не существует.
		if (!file_exists($path)) {
			mkdir($path, 0755, true);
		}
		
		// Если файл уже существует, то дописывается суффикс.
		// @todo сделать проверку на уникальность.
		if (file_exists($path . $filename . '.' . $ext)) {
			$filename .= '_' . substr(md5(microtime(true) . $file['name']), 0, 10);
		}
		
		// Сохранение оригинального файла.
		if ($type == 'img' and $params['compress_original_img']) {
			if (!empty($params['original_convert_to'])) {
				$ext = $params['original_convert_to'];
			}
			
			$Image = new Helper_Image($file['tmp_name'], $path . $filename . '.' . $ext);
			$Image->resize($params['original_resize_width'], $params['original_resize_height'], $params['original_resize_fit'], $params['original_resize_scale']);
		} else {
			copy($file['tmp_name'], $path . $filename . '.' . $ext);
		}

		$sql = "
			INSERT INTO {$this->DB->prefix()}media_files
				(site_id, collection_id, storage_id, filename, original_filename, relative_path, size, upload_datetime, owner_id, mime_type, type)
			VALUES
				('{$this->Env->site_id}', '{$this->collection_id}', '{$this->default_storage_id}', '{$filename}.{$ext}', {$this->DB->quote($file['name'])}, {$this->DB->quote($type_prefix . $relative_path)}, '{$file['size']}', NOW(), '{$this->Env->user_id}', '{$file['type']}' , '$type' ) ";
		$this->DB->query($sql);
		$item_id = $this->DB->lastInsertId();
		
		return $item_id;
	}
	
	/**
	 * Получить тип файла, по его имени.
	 * 
	 * "Тип файла" в данном случае является внутренним определением компонента.
	 *
	 * @param string $filename
	 * @return string - если тип файла неопределён, то вернётся строка 'file'.
	 */
	public function getFileType($filename)
	{
		// Получить расширение файла.
		$ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
		$type = 'file';
		// Попытка определения типа файла.
		foreach ($this->types as $type_name => $ext_list) {
			if (strpos($ext_list, $ext) !== false) {
				$type = $type_name;
				break;
			}
		}
		return $type;
	}
	
	/**
	 * Удалить файл.
	 * 
	 * @param int $file_id
	 * @param array $params
	 * 
	 * @todo в завистимости от типа файла не обрабатывать "original".
	 */
	public function deleteFile($file_id, $params = false)
	{
		$sql = "
			UPDATE {$this->DB->prefix()}media_files SET
				is_deleted  = 1
			WHERE site_id = '{$this->Env->site_id}'
			AND file_id = '$file_id' ";
		$this->DB->query($sql);
		return true;
		/*
		if (strlen($this->getFileUri($file_id)) > 0 and file_exists($_SERVER['DOCUMENT_ROOT'] . $this->getFileUri($file_id)) ) {
			if (@unlink($_SERVER['DOCUMENT_ROOT'] . $this->getFileUri($file_id))) {
				@unlink($_SERVER['DOCUMENT_ROOT'] . $this->getFileUri($file_id, $params));
				
				$sql = "DELETE FROM {$this->DB->prefix()}media_files 
					WHERE file_id = '$file_id' 
					AND site_id = '{$this->Env->site_id}'
					AND collection_id = '{$this->collection_id}'
					";
				$this->DB->exec($sql);
				return true;
			}
		}
		return false;
		*/
	}
	
	/**
	 * Получить список всех коллекций.
	 *
	 * @param bool $get_categories
	 * @return array
	 * 
	 * @todo $get_categories
	 */
	public function getCollectionsList($get_categories = false)
	{
		$data = array();
		$sql = "SELECT * FROM {$this->DB->prefix()}media_collections WHERE site_id = '{$this->Env->site_id}' ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$data[$row->collection_id] = array(
				'default_storage_id'	=> $row->default_storage_id,
				'relative_path'	=> $row->relative_path,
				'name'			=> $row->name,
				'title'			=> $row->title,
				'descr'			=> $row->descr,
				'params'		=> unserialize($row->params),
				);
		}
		return $data;
	}
	
	/**
	 * Получить информацию о коллекции.
	 *
	 * @param int $collection_id
	 * @param bool $get_categories
	 * @return array
	 */
	public function getCollectionData($collection_id = false, $get_categories = false)
	{
		if ($collection_id === false) {
			$collection_id = $this->collection_id;
		}
		
		$data = array();
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}media_collections
			WHERE site_id = '{$this->Env->site_id}'
			AND collection_id = '{$collection_id}' ";
		$result = $this->DB->query($sql);
		
		if ($result->rowCount() == 1) {
			$row = $result->fetchObject();
			$data = array(
				'descr' => $row->descr,
				'name' => $row->name,
				'relative_path' => $row->relative_path,
				'title' => $row->title,
				'default_storage_id' => $row->default_storage_id,
				'params' => empty($row->params) ? $this->params : unserialize($row->params),
				'categories' => $get_categories ? $this->getCategoriesData($collection_id) : array(),
				);
		} else {
			$data = false;
		}
		return $data;
	}
	
	/**
	 * Получить данные категории.
	 *
	 * @param
	 * @return array
	 * 
	 * @todo переименовать в getCategoryData, а также создать метод getCategoriesList.
	 */
	public function getCategoriesData($collection_id = false)
	{
		if ($collection_id === false) {
			$collection_id = $this->collection_id;
		}
		
		$data = array();
		$sql = "SELECT name, title 
			FROM {$this->DB->prefix()}media_categories
			WHERE site_id = '{$this->Env->site_id}'
			AND collection_id = '{$collection_id}' ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$data[$row->name] = $row->title;
		}
	
		return $data;
	}
	
	/**
	 * Получить список файлов.
	 *
	 * @param array $options - формат опций похож на юникатовский.
	 * @return array
	 */
	public function getFilesList(array $options = null)
	{
		// Сортировка
		$sql_order_by = '';
		if (isset($options['order']) and is_array($options['order']) and !empty($options['order'])) {
			$sql_order_by = 'ORDER BY ';
			$sql_order_by_cnt = 0; // @todo убрать
			foreach ($options['order'] as $property_name => $direction) {
				// перед каждым новым полем сортировки надо запятую поставить ;)
				if ($sql_order_by_cnt++ > 0) {
					$sql_order_by .= ', ';
				}

				$sql_order_by .= "$property_name $direction";
			}
			unset($sql_order_by_cnt); // @todo убрать
		}
		
		// Постраничность.
		if (isset($options['paginator']['current_page']) and is_numeric($options['paginator']['current_page'])) {
			$current_page = $options['paginator']['current_page'];
		} else {
			$current_page = 1;
		}
		
		// Записей на страницу (по умолчанию 50)
		$items_per_page = (isset($options['paginator']['items_per_page']) and is_numeric($options['paginator']['items_per_page'])) ? $options['paginator']['items_per_page'] : 50;
		
		if ($items_per_page == 0) {
			$sql_limit = '';
		} else {
			$start_item = ($current_page - 1) * $items_per_page;
			$sql_limit = " LIMIT $start_item, {$items_per_page} ";
		}

		// Извлечение списка файлов из БД.
		$list = array();
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}media_files
			WHERE site_id = '{$this->Env->site_id}'
			AND collection_id = '{$this->collection_id}'
			AND is_deleted = 0
			$sql_order_by
			$sql_limit ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$list[$row->file_id] = array(
				'thumb' => $this->getFileUri($row->file_id, $options['thumb_img_size']),
				'uri' => $this->getFileUri($row->file_id),
				'original_filename' => $row->original_filename,
				'upload_datetime' => $row->upload_datetime,
				'size' => $row->size,
				'type' => $row->type,
				'mime_type' => $row->mime_type,
				'meta' => @unserialize($row->meta),
				'categories' => '@todo сделать категории ;)',
				);
		}
		return $list;
	}
	
	/**
	 * Получить ссылку на файл.
	 * 
	 * @param int $image_id
	 * @param array $options - параметры ...
	 * 
	 * @return string
	 */
	public function getFileUri($file_id, $options = false)
	{
		// ОПИСАНИЕ ФОРМАТА опций
		/*
		$options_zzz = array(
			'w' => 100, // ширина требуемого изображения
			'h' => 100, // высото требуемого изображения
			);
		
		$options_zzz = array(
			'w_min' => 100, // ширина требуемого изображения только уменьшается до указанной 
			'h_max' => 100, // высото требуемого изображения только увеличивается до указанной
			);
		*/
		
		/**
		 * 			c.relative_path AS collection_path,
		 * 				 {$this->DB->prefix()}media_collections AS c
		 * 			AND c.site_id = '{$this->Env->site_id}'
		 * 			AND f.collection_id = c.collection_id 
		 */
		
		$sql = "SELECT
				s.path AS storage_path,
				f.relative_path,
				f.filename,
				f.meta,
				f.type
			FROM {$this->DB->prefix()}media_files AS f,
				 {$this->DB->prefix()}media_storages AS s
			WHERE f.file_id = '$file_id'
			AND f.site_id = '{$this->Env->site_id}'
			AND f.storage_id = s.storage_id
			AND f.collection_id = '{$this->collection_id}'
			AND f.is_deleted = 0 ";
		$result = $this->DB->query($sql);
		
		// @todo сделать поддержку хранилищ.
		if ($result->rowCount() == 1) {
			$row = $result->fetchObject();
			
			$sub_dir = '';
			
			// если тип "картинка", то смотрим в опциях, может быть заказан ресайз.
			if ($row->type == 'img') {
				$width = isset($options['width']) ? $options['width'] : 0;
				$height = isset($options['height']) ? $options['height'] : 0;
				
				if ($width > 0 or $height > 0) {
					if ($width > 0) {
						$sub_dir .= "w$width";
					}
					if ($height > 0) {
						if ($width > 0) {
							$sub_dir .= "-";
						}
						$sub_dir .= "h$height";
					}
				}
			}
			
			$file_uri = HTTP_ROOT . $row->storage_path . $this->collection_path . $row->relative_path . $row->filename; 
			
			// Есть запрос на ресайз картинки.
			if (strlen($sub_dir) > 0) {

				$meta = @unserialize($row->meta);
				
				if (isset($meta['resized_images']) and !empty($meta['resized_images']) and array_key_exists($sub_dir, $meta['resized_images'])) {
					// Пробуем найти уже ресайзнутую картинку
					$file_uri = HTTP_ROOT . $row->storage_path . $this->collection_path . $row->relative_path . $sub_dir . '/' . $row->filename; 
				}
				// Ресайзнутой картинки нет, надо ресайзить и обновить мета в файле.
				else {
					// Создаётся папка '$sub_dir' для хранения ресайзнутых картинок.
					if (!file_exists(DIR_ROOT . $row->storage_path . $this->collection_path . $row->relative_path . $sub_dir . '/')) {
						mkdir(DIR_ROOT . $row->storage_path . $this->collection_path . $row->relative_path . $sub_dir, 0755, true);
					}
					
					$img_original_path = DIR_ROOT . $row->storage_path . $this->collection_path . $row->relative_path . $row->filename; 
					$img_resized_path  = DIR_ROOT . $row->storage_path . $this->collection_path . $row->relative_path . $sub_dir . '/' . $row->filename; 

					$Image = new Helper_Image($img_original_path, $img_resized_path);
					$Image->resize($width, $height);
					
					$file_uri = HTTP_ROOT . $row->storage_path . $this->collection_path . $row->relative_path . $sub_dir . '/' . $row->filename;
					
					// Обновление мета данных в БД.
					$meta['resized_images'][$sub_dir] = filesize($img_resized_path);
					$sql = "
						UPDATE {$this->DB->prefix()}media_files SET
							meta = {$this->DB->quote(serialize($meta))}
						WHERE site_id = '{$this->Env->site_id}'
						AND file_id = '$file_id' ";
					$this->DB->query($sql);
				}
			}
			
			return $file_uri;
		} else {
			return false;
		}
	}
	
	/**
	 * Получить все сведения о файле.
	 *
	 * @param
	 * @return
	 */
	public function getFileData($file_id, array $options = null)  // @todo 
	{
	
		return true;
	}
	
	/**
	 * Установить коллекцию.
	 *
	 * @param int $collection_id
	 */
	public function setCollection($collection_id)
	{
		$this->collection_id = $collection_id;
	}
	
	/**
	 * Проверка является ли коллекция активной т.е. корректно ли оно проинициализировано.
	 *
	 * @return bool
	 */
	public function isActive()
	{
		return $this->collection_id == 0 ? false : true;
	}
}