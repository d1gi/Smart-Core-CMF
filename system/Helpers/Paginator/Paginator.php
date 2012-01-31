<?php
/**
 * Хелпер для отрисовки постраничного вывода.
 * 
 * @version 2011-09-03.0
 */
class Helper_Paginator
{
	/**
	 * Кол-во страниц.
	 */
	protected $pages_count;
	
	/**
	 * Кол-во записей.
	 */
	protected $items_count;
	
	/**
	 * Записей на страницу.
	 */
	protected $items_per_page;
	
	/**
	 * Текущая страница.
	 */
	protected $current_page;
	
	/**
	 * Шаблон ссылки, например "/localhost/page_{PAGE}/" или "?page={PAGE}".
	 * где "{PAGE}" заменяется на номер страницы.
	 * @var string
	 */
	protected $link_tpl;
	
	/**
	 * Отображение ссылки на все записи.
	 */
	public $all;

	/**
	 * Конструктор.
	 * Принимает данные вида:
	 *  - items_count
	 *  - items_per_page
	 *  - current_page
	 *  - link_tpl
	 * 
	 * @param array $data - входные данные.
	 */
	public function __construct(array $data)
	{
		$this->items_count		= $data['items_count'];
		$this->items_per_page	= $data['items_per_page'];
		$this->current_page		= $data['current_page'];
		$this->link_tpl			= $data['link_tpl'];
		$this->all				= isset($data['all']) ? $data['all'] : 'disabled';

		if ($this->all == 'active') {
			$this->current_page = -1;
		}
		
		$this->pages_count = ($this->items_per_page == 0) ? 0 : ceil($this->items_count /  $this->items_per_page);
	}	
	
	/**
	 * NewFunction
	 *
	 * @param
	 */
	public function allPages($is_enable = false)
	{
		if ($is_enable == true or $is_enable == 'active') {
			$this->all == 'active';
		} else {
			$this->all == 'disabled';
		}
	}
	
	/**
	 * Отрисовка хтмл списка 
	 *
	 * @todo установку класса для списка.
	 */
	public function render()
	{
		// Если общее кол-во страниц меньше 2, то не отрисовывается постраничность вообще.
		if ($this->pages_count < 2) {
			return null;
		}
		
		$page_num = 0;
		
		echo "<span title=\"Записей на страницу: {$this->items_per_page}, Всего записей: {$this->items_count}\">Страницы: </span>\n<ul>\n";
		while ($page_num++ < $this->pages_count) {
			// Текущая страница.
			if ($this->current_page == $page_num) {
				echo "\t<li><span>$page_num</span></li>\n";
			} 
			// Остальные страницы.
			else {
				$l = str_replace('{PAGE}', $page_num, $this->link_tpl);
				echo "\t<li><a href=\"$l\">$page_num</a></li>\n";
			}
		}
		
		if ($this->all == 'active') {
			echo "\t<li><span>Все</span></li>\n";
		} elseif ($this->all == 'enable') {
			$l = str_replace('{PAGE}', 'all', $this->link_tpl);
			echo "\t<li><a href=\"$l\">Все</a></li>\n";
		}
		
		echo "</ul>\n";
	}
	
	/**
	 * Отрисовать пагинатор.
	 *
	 * @return text
	 */
	public function __toString()
	{
		ob_start();
		$this->render();
		return ob_get_clean();
	}
	
	/**
	 * Получить кол-во записей на страницу.
	 *
	 * @return int
	 */
	public function getItemsPerPage()
	{
		return $this->items_per_page;
	}
	
	/**
	 * Получить общее кол-во записей.
	 *
	 * @return int
	 */
	public function getItemsCount()
	{
		return $this->items_count;
	}
}