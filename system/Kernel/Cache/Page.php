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
 * @version 	2012-01-05.0
 */
class Cache_Page extends Cache
{
	/**
	 * Конструктор. Синглтон паттерн.
	 */
	protected function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Сохранение страницы кеша.
	 *
	 * @param array $data
	 * @return bool
	 */
	public function save($keys, $data)
	{
		$id    = md5(trim($keys['id']));
		$valid_to_timestamp = time() + $keys['lifetime'];
		
		$this->remove($id, 'page');
		if ($this->create($id, 'page', $valid_to_timestamp)) {
			foreach ($keys['nodes'] as $key => $value) {
				if ($value['is_cached'] == 0) {
					return false;
				}
			}
			
			if (isset($keys['folders'])) {
				$this->createRelation($id, 'page', 'folder', $keys['folders']);
			}
			
			if (isset($keys['nodes'])) {
				$this->createRelation($id, 'page', 'node', $keys['nodes']);
			}

			// Сначала записываются содержимое страницы.
			file_put_contents($this->dir_cache_pages . $id . '.gz', $data['content']);

			// Затем мета данные для неё.
			$meta = array(
				'headers' => $data['headers'],
				'_CREATE_DATETIME' => date('Y-m-d H:i:s', time()), // @todo убрать т.к. это для отладки.
				'valid_to_timestamp' => $valid_to_timestamp,
				'rules' => array(
					'cookies' => array(
						session_name() => 1,
						$this->Cookie->getPrefix() . 'cmf_session_force_start' => 0,
						$this->Cookie->getPrefix() . 'cmf_token' => 0, // @todo сделать адекватную сборку правил запрета куки.
						),
					),
				'is_stat_enable' => false, // Включиить ведение статистики. @todo сделать настраиваемой и через мемкеш.
				);
			file_put_contents($this->dir_cache_pages . $id . '.meta', serialize($meta));
			file_put_contents($this->dir_cache_pages . $id . '.stat', '0');
			
			return true;			
		} else {
			return false;
		}
	}
	
	/**
	 * Удаление страницы из кеша.
	 *
	 * @param string $id
	 */
	static public function delete($id)
	{
		@unlink(DIR_CACHE . 'pages/' . $id . '.meta');
		@unlink(DIR_CACHE . 'pages/' . $id . '.gz');
		@unlink(DIR_CACHE . 'pages/' . $id . '.stat');
	}
}