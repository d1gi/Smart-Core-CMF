<?php
/**
 * Управление блоками.
 * 
 * @author	Artem Ryzhkov
 * @package	Kernel
 * 
 * @uses	DB
 * @uses	Env
 * 
 * @version 2011-09-18.0
 */
class Admin_Block extends Block
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->View->setTpl('block');
	}

	/**
	 * Action....
	 * 
	 * @param string $uri_path
	 * @return array
	 */
	public function run($uri_path)
	{
		if (isset($_POST['action'])) {
			switch ($_POST['action']) {
				case 'update_blocks':
					foreach ($_POST['pd'] as $block_id => $value) {
						// Удаление.
						if (isset($value['delete']) and $value['delete'] == 1) {
							$this->delete($block_id);
							continue;
						}
						
						$name = $this->DB->quote(trim($value['name']));
						$descr = $this->DB->quote(trim($value['descr']));
						
						// Проверка на допустимое значение name.
						if (strlen($name) > 2) {
							// Имя задано, проверяем на уникальность в БД.
							$sql = "SELECT * 
								FROM {$this->DB->prefix()}engine_blocks
								WHERE site_id = '{$this->Env->site_id}'
								AND name = $name
								AND block_id != '$block_id' ";
							$result = $this->DB->query($sql);
							if ($result->rowCount() == 1) {
								continue;
							}
						} else {
							// Имя пустое, по этому дальше не обрабатываем.
							continue;
						}
						
						// Если позиция не число, то устанавливается значение 0.
						if (!is_numeric(trim($value['pos']))) {
							$pos = 0;
						} else {
							$pos = $this->DB->quote(trim($value['pos']));
						}

						// Создание.
						if ($block_id == '_new_block_' and strlen($name) > 2) {
							$this->create($pos, $name, $descr);
							continue;
						}
						
						// Обновление.
						$this->update($pos, $name, $descr, $block_id, $value['inherit']);
					}
					break;
				default;
			}
		}
		
		$uri_path_parts = explode('/', $uri_path);
		switch ($uri_path_parts[0]) {
			case '':
				$this->View->blocks = array();
				$sql = "SELECT * 
					FROM {$this->DB->prefix()}engine_blocks
					WHERE site_id = '{$this->Env->site_id}'
					ORDER BY pos ";
				$result = $this->DB->query($sql);
				while ($row = $result->fetchObject()) {
					// Вычисление кол-ва нод включенных в блок.
					$sql2 = "SELECT node_id
						FROM {$this->DB->prefix()}engine_nodes
						WHERE site_id = '{$this->Env->site_id}'
						AND block_id = '$row->block_id' ";
					$result2 = $this->DB->query($sql2);
					$nodes_count = $result2->rowCount();
					
					$inherit = '';
					$sql2 = "SELECT folder_id
						FROM {$this->DB->prefix()}engine_blocks_inherit
						WHERE site_id = '{$this->Env->site_id}'
						AND block_id = '$row->block_id' ";
					$result2 = $this->DB->query($sql2);
					$cnt = $result2->rowCount();
					while ($row2 = $result2->fetchObject()) {
						$inherit .= $row2->folder_id;
						if (--$cnt) {
							$inherit .= ',';
						}
					}
					$this->View->blocks[$row->block_id] = array(
						'pos'		  => $row->pos,
						'name'		  => $row->name,
						'descr'		  => $row->descr,
						'nodes_count' => $nodes_count,
						'inherit'	  => $inherit,
						);
				}
				break;
			default;
		}
	}
	
	/**
	 * Создание.
	 *
	 * @param int $pos
	 * @param string $name
	 * @param string $descr
	 */
	public function create($pos, $name, $descr)
	{
		$sql = "
			INSERT INTO {$this->DB->prefix()}engine_blocks
				(site_id, pos, name, descr, create_datetime, owner_id )
			VALUES
				('{$this->Env->site_id}', $pos, $name, $descr, NOW(), {$this->Env->user_id} ) ";
		$this->DB->query($sql);
		return true;
	}
	
	/**
	 * Удаление
	 *
	 * @param int $block_id
	 */
	public function delete($block_id)
	{
		$sql = "DELETE FROM {$this->DB->prefix()}engine_blocks
			WHERE site_id = '{$this->Env->site_id}'
			AND block_id = '$block_id' ";
		$this->DB->exec($sql);

		$sql = "DELETE FROM {$this->DB->prefix()}engine_blocks_inherit
			WHERE site_id = '{$this->Env->site_id}'
			AND block_id = '$block_id' ";
		$this->DB->exec($sql);
	}
	
	/**
	 * Обновление.
	 *
	 * @param int $pos
	 * @param string $name
	 * @param string $descr
	 * @param int $block_id
	 * @param string $inherit
	 */
	public function update($pos, $name, $descr, $block_id, $inherit = null)
	{
		$sql = "
			UPDATE {$this->DB->prefix()}engine_blocks SET
				name = $name,
				descr = $descr,
				pos = $pos
			WHERE site_id = '{$this->Env->site_id}'
			AND block_id = '$block_id' ";
		$this->DB->query($sql);
		
		// Сначала удаляются все записи по наследованию.
		$sql = "DELETE FROM {$this->DB->prefix()}engine_blocks_inherit
			WHERE site_id = '{$this->Env->site_id}'
			AND block_id = '$block_id' ";
		$this->DB->exec($sql);
		// Затем создаются новые записи.
		if (strlen(trim($inherit)) > 0) {
			$inherit = explode(',', trim($inherit));
			foreach ($inherit as $folder_id) {
				$sql = "
					INSERT INTO {$this->DB->prefix()}engine_blocks_inherit
						(folder_id, site_id, block_id )
					VALUES
						('$folder_id', '{$this->Env->site_id}', '$block_id' ) ";
				$this->DB->query($sql);
			}
		}
	}
}