<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Модуль работы с типовым HTML текстом.
 * 
 * @uses EE
 * @uses Log
 * @uses Response
 * @uses User
 * 
 * @package Module
 * 
 * @version 2011-12-27.0
 */
class Module_Texter extends Module
{
	/**
	 * Для каждого экземпляра ноды хранится ИД текстовой записи.
	 * @var int
	 */
	protected $text_item_id;
	
	/**
	 * Какой редактор использовать.
	 * !!!note: пока используется как флаг, где 0 - не использовать визивиг, а 1 - использовать.
	 * @var string
	 */
	protected $editor;
	
	/**
	 * Конструктор.
	 * 
	 * @return void
	 */
	protected function init()
	{
		$this->setVersion(0.4);
		
		$this->Node->setDefaultParams(array(
			'text_item_id' => 0,
			'editor' => 1,
			));

		$this->text_item_id	= $this->Node->getParam('text_item_id');
		$this->editor		= $this->Node->getParam('editor');
	}
	
	/**
	 * Запуск модуля.
	 * 
	 * @return void
	 */
	public function run($parser_data)
	{
		$text_item = $this->getText($this->text_item_id);

		if (is_array($text_item['meta']) and !empty($text_item['meta'])) {
			foreach ($text_item['meta'] as $key => $value) {
				$this->EE->addHeadMeta($key, $value);
			}
		}
		
		$this->output_data['text'] = $text_item['text'];
	}	
	
	/**
	 * Ajax.
	 *
	 * @param string $uri_path - часть URI, адресованная модулю.
	 * @return
	 */
	public function ajax($uri_path)
	{
		if (!empty($_POST)) {
			$this->Response->setMimeType('text/plain');
			$this->Response->setDirectData('POST ok');
			return;
		}
		
		$uri_path_parts = explode('/', $uri_path);
		$action = $uri_path_parts[0];
		
		switch ($action) {
			case 'get':
				$this->Response->setMimeType('text/plain');
				$text_item = $this->getText($this->text_item_id);
				$this->Response->setDirectData($text_item['text']);
				break;
			default;
		}
	}
	
	/**
	 * Обработчик хуков.
	 *
	 * @param string $method - имя вызываемого метода.
	 * @param array $args - массив с аргументами.
	 */
	public function hook($method, $args = null)
	{
		$text = false;
		switch ($method) {
			case 'getText': // @return string
				if (empty($args)) {
					$tmp = $this->getText($this->text_item_id);
					$text = $tmp['text'];
				} else {
					$tmp = $this->getText($args['id']);
					$text = $tmp['text'];
				}
				break;
			default;
		}
		return $text;
	}
	
	/**
	 * Получение текста из базы.
	 * 
	 * @uses Log
	 * 
	 * @param int $item_id
	 * @return text
	 * 
	 * @todo мультиязычность.
	 */
	protected function getText($item_id)
	{
		$sql = "
			SELECT text, item_id, meta
			FROM {$this->DB->prefix()}text_items
			WHERE item_id = '$item_id'
			AND language_id = '{$this->Env->language_id}'
			AND site_id = '{$this->Env->site_id}' ";
		if ($row = $this->DB->getRow($sql)) {
			return $row;
		} else {
			$stack = "\nFile: " . __FILE__ . "\nLine: ". __LINE__ . "\nClass: " . __CLASS__ . "\nMethod: " . __METHOD__;
			Log::getInstance()->write('system', "Message: item_id = $item_id is not accessible. $stack");
			return false;
		}
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
			case 'save':
				$this->updateText($this->Node->params['text_item_id'], trim($pd['text']));
				break;
			case 'create_meta':
				$this->createMeta($this->Node->params['text_item_id'], $_POST['pd']);
				break;
			case 'update_meta_tags':
				$this->updateMeta($this->Node->params['text_item_id'], $_POST['pd']);
				break;
			default:
		}
	}
	
	/**
	 * Обновление мета-тэгов.
	 *
	 * @param int $text_item_id - id текстовой записи.
	 * @param array $pd - массив данных.
	 * @return bool
	 */
	public function updateMeta($text_item_id, $pd)
	{
		$meta = array();
		foreach ($pd as $key => $value) {
			if (isset($value['delete']) and (string) $value['delete'] === '0') {
				continue;
			}
			$meta[$key] = $value;
		}
		
		$meta = count($meta) == 0 ? 'NULL' : $this->DB->quote(serialize($meta));
		
		$sql = "
			UPDATE {$this->DB->prefix()}text_items SET
				meta = {$meta}
			WHERE item_id = '$text_item_id'
			AND language_id = '{$this->Env->language_id}'
			AND site_id = '{$this->Env->site_id}' ";
		$this->DB->exec($sql);
		return true;
	}

	/**
	 * Создание мета-тэга.
	 *
	 * @param int $text_item_id - id текстовой записи.
	 * @param array $pd - массив данных.
	 * @return bool
	 */
	public function createMeta($text_item_id, $pd)
	{
		$text_item = $this->getText($text_item_id);
		
		$meta = empty($text_item['meta']) ? false : $text_item['meta'];

		// Проверка на существующий тэг.
		if (isset($meta[strtolower($pd['name'])])) {
			// @todo вывод в систему сообщений об ошибках.
			// echo "Такой тэг уже существует";
			return false;
		} else {
			$meta[strtolower($pd['name'])] = $pd['content'];
			$meta = $this->DB->quote(serialize($meta));
			$sql = "
				UPDATE {$this->DB->prefix()}text_items SET
					meta = {$meta}
				WHERE item_id = '$text_item_id'
				AND language_id = '{$this->Env->language_id}'
				AND site_id = '{$this->Env->site_id}' ";
			$this->DB->exec($sql);
			return true;
		}
	}

	/**
	 * Cохранение текста.
	 * 
	 * @uses User
	 * 
	 * @param int $item_id
	 * @param text $text
	 * @return void
	 * 
	 * @todo мультиязычность.
	 */
	protected function updateText($item_id, $text)
	{
		$sql = "
			UPDATE {$this->DB->prefix()}text_items SET
				text = {$this->DB->quote($text)}
			WHERE item_id = '$item_id'
			AND language_id = '{$this->Env->language_id}'
			AND site_id = '{$this->Env->site_id}'";
		// В случае успешного обновления, записываем "историю".
		if ($this->DB->exec($sql) == 1) {
			$unpack_length = strlen($text);
			$sql = "
				INSERT INTO	{$this->DB->prefix()}text_items_history
					(language_id, site_id, text_archive, unpack_length, item_id, update_time, user_id)
				VALUES
					('{$this->Env->language_id}', '{$this->Env->site_id}', {$this->DB->quote(gzcompress($text, 9))}, '$unpack_length', '$item_id', now(), {$this->Env->user_id}) ";
			$this->DB->exec($sql);
		} else {
			// @todo случай НЕ успешного обновления.
		}
	}
	
	/**
	 * Создание новой текстовой записи
	 *
	 * @param void
	 * @return int - ID созданной записи.
	 */
	protected function createText()
	{
		$sql = "
			INSERT INTO	{$this->DB->prefix()}text_items
				(text, language_id, site_id, owner_id, create_datetime)
			VALUES
				('', '{$this->Env->language_id}', '{$this->Env->site_id}', '{$this->Env->user_id}', NOW() ) ";
		$this->DB->query($sql);
		return $this->DB->lastInsertId();
	}	
}