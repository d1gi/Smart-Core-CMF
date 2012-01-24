<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Компонент позволяющий создавать разнообразные вспплывающие окна.
 * 
 * @uses EE
 */
class Component_Popup extends Controller
{
	private $type;
	private $title;
	private $descr;
	private $width = 980;
	private $height = 700;
	private $href;
	private $link_name;
	private $on_mouse_out;
	private $on_mouse_over;
	
//	public function __construct($type = 'highslide')
	public function __construct($type = 'lightview')
	{
		$this->setType($type);
	}
	
	/**
	 * Установить тип библиотери по работе с лайтбоксом.
	 * 
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = $type;
		switch ($this->type) {
			case 'lightview':
				$this->EE->useScriptLib('lightview');
				break;
			/*
			case "lightwindow":
				$HeadExtra->setData("prototype", "<script type=\"text/javascript\" src=\"" . HTTP_SCRIPTS . "prototype-1.6.0.3.js\"></script>\n");
				$HeadExtra->setData("scriptaculous", "<script type=\"text/javascript\" src=\"" . HTTP_SCRIPTS . "scriptaculous/scriptaculous.js\"></script>\n");
				$HeadExtra->setData("lightwindow", "
				<link rel=\"stylesheet\" type=\"text/css\" href=\"" . HTTP_SCRIPTS . "lightwindow/css/lightwindow.css\" />
				<script type=\"text/javascript\" src=\"" . HTTP_SCRIPTS . "lightwindow/javascript/lightwindow.js\"></script>\n
				");
				break;
			case "lytebox":
				$HeadExtra->setData("lytebox", "
				<link rel=\"stylesheet\" type=\"text/css\" href=\"" . HTTP_SCRIPTS . "lytebox/lytebox.css\" />
				<script type=\"text/javascript\" src=\"" . HTTP_SCRIPTS . "lytebox/lytebox.js\"></script>\n
				");
				break;
			*/
			case 'highslide':
				$Popup = new Component_Popup_Highslide();
				$Popup->setHtmlHead();
				break;
			case 'js':
				break;
			default:
		}
	}
	
	public function setTitile($title)
	{
		$this->title = $title;
	}
	
	public function setDescr($descr)
	{
		$this->descr = $descr;
	}
	
	public function setSize($width, $height)
	{
		$this->width = $width;
		$this->height = $height;
	}
	
	public function setHref($href)
	{
		$this->href = $href;
	}
	
	public function setLinkName($link_name)
	{
		$this->link_name = $link_name;
	}
	
	public function setOnMouseOver($on_mouse_over)
	{
		$this->on_mouse_over = $on_mouse_over;
	}
	
	public function setOnMouseOut($on_mouse_out)
	{
		$this->on_mouse_out = $on_mouse_out;
	}
	
	/**
	* Получаем ссылку на открытие окна.
	* 
	* @return string
	*/
	public function getLink()
	{
		$link = '';
		switch ($this->type) {
			case 'lightview':
				$link .= "<a href=\"$this->href\" class=\"lightview\" rel=\"iframe\"\n";
				$link .= "title=\"$this->title 
					:: $this->descr 
					:: width: $this->width, height: $this->height 
					\"";
				break;
			/*
			case "lightwindow":
				$link .= "<a href=\"$this->href\" class=\"lightwindow page-options\" rel=\"iframe\"\n";
				$link .= "title=\"$this->title\" params=\"lightwindow_width=800,lightwindow_height=600,lightwindow_type=external\"";
				break;
			case "lytebox":
				$link .= "<a href=\"$this->href\" rel=\"lyteframe\"\n";
				$link .= "title=\"$this->title\" rev=\"width: {$this->width}px; height: {$this->height}px; scrolling: no;\"";
				break;
			*/
			case 'highslide':
				$Popup = new Component_Popup_Highslide();
				$link = $Popup->getLink($this->href, $this->title, $this->width, $this->height);
				break;
			case 'js':
				$link .= "<a title=\"$this->title\" style=\"cursor:pointer\" ";
				$link .= "onClick=\"window.open('$this->href','mywindow','width=$this->width,height=$this->height,status=no,resizable=no,scrollbars=no,toolbar=no,location=no,menubar=no,top=100,left=240')\" ";
				break;
			default:
		}
		
		if(isset($this->on_mouse_over)) {
			$link .= " onmouseover=\"$this->on_mouse_over\" ";
		}
		
		if(isset($this->on_mouse_out)) {
			$link .= " onmouseout=\"$this->on_mouse_out\" ";
		}
		
		// @todo может быть лучше убрать этот кусок в классы библиотек?
		$link .= ">$this->link_name</a>";
		return $link;
	}	
	
}
