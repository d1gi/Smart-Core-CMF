<?php
/**
 * Выполнение задач по расписанию.
 * 
 * @author		Artem Ryzhkov
 * @package		Kernel
 * @copyright	Copyright &copy; 2010-2012 Smart Core CMF 
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @uses		DB
 * @uses		Node
 * @uses		Site
 * 
 * @version 	2011-11-19.2
 */
class Cron extends Controller
{
	protected $current_site_id;
	protected $lock_file;
	protected $status_file;
	
	/**
	 * Constructor.
	 *
	 * @param array $config
	 * @return
	 */
	public function __construct()
	{
		parent::__construct();
		$this->current_site_id	= $this->Env->site_id;
		$this->lock_file		= DIR_VAR_PLATFORM . 'cron.lock';
		$this->status_file		= DIR_VAR_PLATFORM . 'cron.status';
	}

	/**
	 * Запуск.
	 *
	 * @param
	 * @return
	 */
	public function run()
	{
		if (file_exists($this->lock_file)) {
			$lock_file_data = unserialize(file_get_contents($this->lock_file));
			if ($lock_file_data['valid_to_timestamp'] > time()) {
				return true;
			}
		}
		
		set_time_limit(50);
		@ignore_user_abort(true);
		
		$crontab = array();
		$minutes = time() / 60;
		$current_minutes = ceil($minutes);
		
		$sql = "SELECT * FROM {$this->DB->prefix()}engine_crontab WHERE is_active = 1 ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			// Вычисление минут, когда надо запустить задачу.
			$task_minutes = ceil($minutes / $row->tmp_period_in_min) * $row->tmp_period_in_min;

			if ($current_minutes == $task_minutes) {
				$crontab[$row->task_id] = array(
					'title'		=> $row->title,
					'cron'		=> $row->cron,
					'period_in_min' => $row->tmp_period_in_min,
					'params'	=> $row->params,
					);
			}
		}
		
		file_put_contents($this->lock_file, serialize(array(
			'valid_to_timestamp' => time() + 50, // Блокировка на 50 секунд.
			)));
		
		foreach ($crontab as $task_id => $task) {
			$params = unserialize($task['params']);
			switch ($params['exec']) {
				case 'file':
					$exec = str_replace('{DIR_SYSTEM}', DIR_SYSTEM, $params['file']);
					include($exec);
					break;
				case 'class':
					$Task = new $params['class'];
					$Task->cron();
					break;
				case 'node':
					Site::init($params['site_id']);
					$Node = new Node();
					$Module = $Node->getModuleInstance($params['node_id']);
					$Module->cron();
					break;
				default;
			}
		}
		
		return true;
	}
	
}