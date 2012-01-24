<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Модуль новостей.
 * 
 * @uses Component_DatePicker
 * @uses Component_Unicat
 * @uses DB
 * 
 * @version 2011-12-14.0
 */
class Module_News extends Module_Catalog_Admin
{
	/**
	 * Использовать даты начала и окончания публикации?
	 * @var bool
	 */
	protected $use_publication_period;
	
	/**
	 * Конструктор.
	 */
	protected function init()
	{
		parent::init();
		$this->Node->addDefaultParam('use_publication_period', 0);
	}
	
	/**
	 * Запуск модуля.
	 * 
	 * @return void
	 */
	public function run($parser_data)
	{
		// По умолчанию применяется обратная сортировка.
		$options = array(
			'order' => array(
				'datetime'=> 'DESC', 
				),
			);
		
		if ($this->use_publication_period) {
			$Date = new Helper_Date();
			$options['filters'] = array(
				array('date_start', '<=', $Date->getDatetime() ),
				array('date_start', 'IS NULL'),
				array('date_end', '>=', $Date->getDatetime() ),
				array('date_end', 'IS NULL'),
				);
		}
		
		$this->_catalogRun($parser_data, $options);
	}
		
	/**
	 * Обработчик хуков.
	 *
	 * @param string $method - имя вызываемого метода.
	 * @param array $args - массив с аргументами.
	 */
	public function hook($method, $args = false)
	{
		switch ($method) {
			case 'getItems':
				$options = array(
					'order' => array(
						'datetime'=> 'DESC', 
						),
					'paginator' => array(
						'items_per_page' => $args['items_per_page'],
						'current_page' => 1,
						),
					);
				return $this->Unicat->getItems($options);

				break;
			default;
				return parent::hook($method, $args);
		}
		return false;
	}
		
}