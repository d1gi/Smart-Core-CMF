<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс по работе с мета данными.
 * 
 * @version 2011-07-18.0
 */
class Component_Meta
{
	/**
	 * Массив мета данных.
	 * @var array
	 */
	protected $meta;
	
	/**
	 * Конструктор.
	 *
	 * @param array $meta - массив мета данных.
	 */
	public function __construct($meta = null)
	{
		$this->meta = $meta;
	}
	
	/**
	 * Получить элементы управления мета данными.
	 *
	 * @param array $hiddens - скрытые элементы формы.
	 * @return array
	 */
	public function getControls(array $hiddens = null)
	{
		$controls = array(
			'meta_list' => $this->meta,
			'meta_create_form' => array(
				'hiddens' => $hiddens,
				'elements' => array(
					'pd[name]' => array(
						'label' => 'Имя',
						'type' => 'string',
						'value' => '',
						),
					'pd[content]' => array(
						'label' => 'Значение',
						'type' => 'string',
						'value' => '',
						),
					),
				'buttons' => array(
					'submit[create_meta]' => array(
						'value' => 'Создать Мета-тэг',
						'type' => 'submit',
						),
					),
				'help' => 'Cправка по добавлению мета тэга.',
				),
			);
		return $controls;
	}
	
	/**
	 * Отображение интерфейса управления мета тэгами.
	 *
	 * @param array $data
	 * @return void
	 */
	public function renderControls($data)
	{
		include 'Edit.tpl';
	}
}
