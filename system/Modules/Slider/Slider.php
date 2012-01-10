<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Module Slider.
 * 
 * @package Module
 * 
 * @version 2011-06-29.0
 */
class Module_Slider extends Module
{
	/**
	 * Время задержки в мс.
	 * @var int
	 */
	protected $time_interval;
	
	/**
	 * Конструктор.
	 * 
	 * @return void
	 */
	protected function init()
	{
		$this->time_interval = $this->Node->params['time_interval'];
	}
	
	/**
	 * Запуск модуля.
	 * 
	 * @return void
	 */
	public function run($parser_data)
	{
		$this->output_data['time_interval'] = $this->time_interval;
		$this->output_data['slides'] = $this->getSlides();
	}	
	
	/**
	 * Получить все слайды.
	 *
	 * @param void
	 * @return array
	 */
	protected function getSlides()
	{
		$slides = array();
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}slider
			WHERE site_id = '{$this->Env->site_id}'
			AND group_id = 1
			ORDER BY pos ASC
			";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$slides[$row->slider_id] = array(
				'img' => '/images/slider/' . $row->img,
				'name' => $row->img,
				'caption' => $row->caption,
				'pos' => $row->pos,
				);
		}
		return $slides;
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
			case 'add':
				$path = DIR_ROOT . 'images/slider/' . $_FILES['pd']['name']['img'];
				copy($_FILES['pd']['tmp_name']['img'], $path);
				$sql = "SELECT max(pos) AS max_pos
					FROM {$this->DB->prefix()}slider
					WHERE site_id = '{$this->Env->site_id}'
					AND group_id = 1
					";
				$result = $this->DB->query($sql);
				$row = $result->fetchObject();
				$max_pos = $row->max_pos + 1;
				
				$sql = "
					INSERT INTO {$this->DB->prefix()}slider
						(site_id, group_id, img, pos)
					VALUES
						('{$this->Env->site_id}', 1, '{$_FILES['pd']['name']['img']}', $max_pos)
					";
				$this->DB->query($sql);
				break;
			case 'recalculate':
				foreach ($pd as $key => $value) {
					if (!is_int((int) $value)) {
						continue;
					}
					
					$sql = "
						UPDATE {$this->DB->prefix()}slider SET
							pos = '$value'
						WHERE site_id = '{$this->Env->site_id}'
						AND group_id = 1
						AND slider_id = '$key'
						";
					$this->DB->query($sql);
				}
				break;
			default:
		}
	} 	
}
