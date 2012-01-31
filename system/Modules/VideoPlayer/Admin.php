<?php 
/**
 * Класс с административными методами.
 * 
 * @version 2011-06-29.0
 */
class Module_VideoPlayer_Admin extends Module_VideoPlayer implements Admin_ModuleInterface
{
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array $node_params
	 */
	public function getParams()
	{
		return array(
			'uri' => array(
				'label' => 'Ссылка:',
				'type' => 'text',
				'value' => $this->uri,
				),
			'width' => array(
				'label' => 'Ширина:',
				'type' => 'text',
				'value' => $this->width,
				),
			'height' => array(
				'label' => 'Высота:',
				'type' => 'text',
				'value' => $this->height,
				),
			);
	}
}