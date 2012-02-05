<?php
/**
 * Модуль отображения карты Google.
 * 
 * Настройки хранятся в параметрах подключения модуля.
 * 
 * @package Module
 * @version 2011-10-01.0
 */
class Module_GoogleMap extends Module
{
	/**
	 * Ключ Google Map API.
	 * @var int
	 */
	protected $google_key;
	
	// Содержимое информационного окошка.
	protected $info_window;
	
	/**
	 * Долгота.
	 * @var double
	 */
	protected $longitude;
	
	/**
	 * Широта.
	 * @var double
	 */
	protected $latitude;
	
	/**
	 * Блок в котором будет отрисовываться карта.
	 */
	protected $map_block_id_name = 'google_map_canvas';
	protected $map_block_width	 = '100%'; // @todo 
	protected $map_block_height  = '500px'; // @todo 
	
	/**
	 * Масштаб.
	 * @var int
	 */
	protected $scale = 16;
	
	/**
	 * Конструктор.
	 */
	protected function init()
	{
		$this->setVersion(0.1);
		/* $this->google_key = $this->Node->params['google_key']; */
		$this->info_window = $this->Node->params['info_window'];
		$this->longitude = $this->Node->params['longitude'];
		$this->latitude = $this->Node->params['latitude'];
		$this->scale = $this->Node->params['scale'];
	}
	
	/**
	 * Запуск модуля.
	 */
	public function run($params)
	{
		$sql = "SELECT * FROM {$this->DB->prefix()}google_map_keys WHERE domain = '" . HTTP_HOST . "' ";
		$result = $this->DB->query($sql);

		if ($result->rowCount() == 1) {
			$row = $result->fetchObject();
			
			$this->ScriptsLib->request('jquery');
			$this->Html->setBodyAttribute['onload'] = 'initialize()';
			$this->Html->setBodyAttribute['onunload'] = 'GUnload()';
			$this->Html->addHeadData('<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=true&amp;key=' . $row->key . '" type="text/javascript"></script>' . "\n\t" .
				'<script type="text/javascript">// <![CDATA[' . "\n\t" .
				'function initialize() {' . "\n\t" .
				'if (GBrowserIsCompatible()) {' . "\n\t" .
				'var map = new GMap2(document.getElementById("' . $this->map_block_id_name . '"));' . "\n\t" .
				'map.setCenter(new GLatLng(' . $this->longitude . ', ' . $this->latitude . '), ' . $this->scale . ');' . "\n\t" .
				'map.setUIToDefault();' . "\n\t" .
				'map.setMapType(G_HYBRID_MAP);' . "\n\t" .
				'map.openInfoWindow(map.getCenter(), document.createTextNode("' . $this->info_window . '"));' . "\n\t" .
				'}' . "\n\t" .
				'}' . "\n\t" .
				'// ]]></script>'
				);
		}
		
		$this->View->map_block = $this->map_block_id_name;
	}
}
