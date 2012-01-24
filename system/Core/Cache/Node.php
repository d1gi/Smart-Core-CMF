<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Кеширование страниц целиком.
 * 
 * @author		Artem Ryzhkov
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @version 	2012-01-04.0
 */
class Cache_Node extends Cache
{
	/**
	 * Конструктор. Синглтон паттерн.
	 */
	protected function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * NewFunction
	 *
	 * @param
	 * @return
	 */
	public function loadHtml($id)
	{
		if ($id === null) {
			return null;
		}
		
		$id = md5(serialize($id));
		
		$file = $this->dir_cache_nodes . $id;
		
		// Чтение мета данных.
		if (!$this->check($id, 'node_html') or !file_exists($file . '.meta')) {
			return false;
		}

		$meta = unserialize(file_get_contents($file . '.meta'));
		
		// Устаревший кеш.
		if ($meta['valid_to_timestamp'] < time()) {
			self::delete($id);
			return false;
		}
		
		// Отдача кеша.
		return file_exists($file . '.html') ? file_get_contents($file . '.html') : null;
	}
	
	/**
	 * Сохранение страницы кеша.
	 *
	 * @param array $data
	 * @return int - ID созданного кеша
	 */
	public function saveHtml($keys, $data)
	{
		$id = md5(serialize($keys['id']));
		$valid_to_timestamp = time() + $keys['lifetime'];
		$this->remove($id, 'node_html');
		$this->create($id, 'node_html', $valid_to_timestamp);
		
		if (isset($keys['folders'])) {
			$this->createRelation($id, 'node_html', 'folder', $keys['folders']);
		}
		
		if (isset($keys['nodes'])) {
			$this->createRelation($id, 'node_html', 'node', $keys['nodes']);
		}
		
		// Сначала записываются содержимое страницы.
		file_put_contents($this->dir_cache_nodes . $id . '.html', $data);

		// Затем мета данные для неё.
		$meta = array(
			'_create_datetime' => date('Y-m-d H:i:s', time()), // @todo убрать т.к. это для отладки.
			'valid_to_timestamp' => $valid_to_timestamp,
			'lifetime' => $keys['lifetime'],
			);
		file_put_contents($this->dir_cache_nodes . $id . '.meta', serialize($meta));
		return true;
	}
	
	/**
	 * Удаление из кеша.
	 *
	 * @param string $id
	 */
	static public function delete($id)
	{
		@unlink(DIR_CACHE . 'nodes/' . $id . '.meta');
		@unlink(DIR_CACHE . 'nodes/' . $id . '.html');
	}

}