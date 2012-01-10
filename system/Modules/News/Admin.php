<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс с административными методами.
 * 
 * @version 2011-12-05.0
 */
class Module_News_Admin extends Module_News
{
	/**
	 * Конструктор.
	 */
	protected function init()
	{
		$this->unicat_requred_items_properties = array(
			'news' => array( // В верхнем уровне перечисляются группы свойств.
				'title' => 'Новости',
				'properties' => array(
					'title' => array(
						'title'			=> 'Заголовок',
						'pos'			=> 0,
						'type'			=> 'string',
						'is_required'	=> 1,
						'show_in_admin'	=> 1,
						'show_in_list'	=> 1,
						'show_in_view'	=> 1,
						'empty_as_null'	=> 0,
						),
					'datetime' => array(
						'title'			=> 'Дата',
						'pos'			=> 1,
						'type'			=> 'datetime',
						'params'		=> array(
							'default' => 'datetime',
							),
						'is_required'	=> 1,
						'show_in_admin'	=> 1,
						'show_in_list'	=> 1,
						'show_in_view'	=> 1,
						'empty_as_null'	=> 0,
						),
					'date_start' => array(
						'title'			=> 'Дата начала',
						'pos'			=> 2,
						'type'			=> 'datetime',
						'is_required'	=> 0,
						'show_in_admin'	=> 0,
						'show_in_list'	=> 0,
						'show_in_view'	=> 0,
						'empty_as_null'	=> 1
						),
					'date_end' => array(
						'title'			=> 'Дата окончания',
						'pos'			=> 3,
						'type'			=> 'datetime',
						'is_required'	=> 0,
						'show_in_admin'	=> 0,
						'show_in_list'	=> 0,
						'show_in_view'	=> 0,
						'empty_as_null'	=> 1
						),
					'announce' => array(
						'title'			=> 'Аннотация',
						'pos'			=> 4,
						'type'			=> 'text',
						'is_required'	=> 0,
						'show_in_admin'	=> 0,
						'show_in_list'	=> 1,
						'show_in_view'	=> 1,
						'empty_as_null'	=> 0
						),
					'text' => array(
						'title'			=> 'Полный текст',
						'pos'			=> 5,
						'type'			=> 'text',
						'is_required'	=> 0,
						'show_in_admin'	=> 0,
						'show_in_list'	=> 0,
						'show_in_view'	=> 1,
						'empty_as_null'	=> 0
						),
					),
				),
			);
		parent::init();
	}
	
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		$node_params = parent::getParams();
		$node_params['use_publication_period'] = array(
				'label' => 'Использовать даты начала и окончания публикации?',
				'type' => 'checkbox',
				'value' => $this->Node->getParam('use_publication_period'),
				);
		return $node_params;
	}

	/**
	 * Функция обработки действий над нодой.
	 * 
	 * @param string $params - часть адреса идущая после ключевого слова "action" в строке запроса.
	 * @return void
	 */
	public function nodeAction($params)
	{
		$this->output_data = $this->Unicat->action($params);
		
		// Если не используется период публикации, то и не отображаются свойства дат начала и конца отображения.
		if ($this->use_publication_period == 0) {
			unset($this->output_data['form_data']['elements']['pd[content][8]']);
			unset($this->output_data['form_data']['elements']['pd[content][9]']);
		}
		
		$this->setTpl($this->Unicat->getTpl());
		$this->setTplPath($this->Unicat->getTplPath());
		return true;
	}

	/**
	 * Вызывается при создании ноды.
	 * 
	 * @return array $params
	 *
	public function createNode()
	{
		$params = parent::createNode();
		//$params['use_publication_period'] = 0;
		return $params;
	}
	*/

}