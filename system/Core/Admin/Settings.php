<?php
/**
 * Управление настройками.
 * 
 * @author	Artem Ryzhkov
 * @package	Kernel
 * 
 * @uses	DB
 * 
 * @version 2012-01-24.0
 */
class Admin_Settings extends Controller
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->View->setTpl('settings');
	}

	/**
	 * Получить список всех настроек.
	 *
	 * @param string $variable
	 * @return array 
	 */
	public function getSettingsData($variable = '')
	{
		if ($variable != '') {
			$variable = " AND variable = '$variable' ";
		}

		$data = array();
		
		$sql = "SELECT * FROM {$this->DB->prefix()}engine_settings ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$sql2 = "SELECT * 
				FROM {$this->DB->prefix()}engine_settings_values
				WHERE site_id = '{$this->Env->site_id}'
				AND variable = '{$row->variable}' ";
			$result2 = $this->DB->query($sql2);
			if ($result2->rowCount() == 1) {
				$row2 = $result2->fetchObject();
				$value = $row2->value;
			} else {
				$value = $row->default_value;
			}

			$data[$row->variable] = array(
				'value'			=> $value,
				'default_value'	=> $row->default_value,
				'descr'			=> $row->descr,
				);
		}
		
		return $data;
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
				case 'update_settins':
					$settings = $this->getSettingsData();
					foreach ($_POST['pd'] as $key => $value) {
						// Есть изменения
						if ($value != $settings[$key]['value']) {
							// Значение по умолчанию.
							if ($value == $settings[$key]['default_value']) {
								$sql = "DELETE FROM {$this->DB->prefix()}engine_settings_values
									WHERE site_id = '{$this->Env->site_id}'
									AND variable = '$key' ";
								$this->DB->exec($sql);
							} else {
								// Проверка, есть ли заданная настройка в таблице
								$sql2 = "SELECT * 
									FROM {$this->DB->prefix()}engine_settings_values
									WHERE site_id = '{$this->Env->site_id}'
									AND variable = '{$key}' ";
								$result2 = $this->DB->query($sql2);
								$value_quoted = $this->DB->quote(trim($value));
								if ($result2->rowCount() == 1) {
									$sql = "
										UPDATE {$this->DB->prefix()}engine_settings_values SET
											value = $value_quoted
										WHERE site_id = '{$this->Env->site_id}'
										AND variable = '{$key}' ";
									$this->DB->query($sql);
								} else {
									$sql = "
										INSERT INTO {$this->DB->prefix()}engine_settings_values
											(variable, site_id, value )
										VALUES
											('{$key}', '{$this->Env->site_id}', $value_quoted ) ";
									$this->DB->query($sql);
								}
								
							}
						}
					}
					break;
				default;
			}
		}
		
		
		$uri_path_parts = explode('/', $uri_path);
		switch ($uri_path_parts[0]) {
			case '':
				$this->View->settings = $this->getSettingsData();
				break;
			default;
		}
	}
	
}