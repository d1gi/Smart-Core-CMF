<?php 
/**
 * Хелпер для отрисовки форм.
 * 
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://www.opensource.org/licenses/gpl-2.0
 * @package		Helper
 * 
 * @version 2011-12-03.0
 */
class Helper_Form
{
	/**
	 * Данные формы.
	 * @var array
	 */
	protected $data;
	
	/**
	 * Аттрибуты тега <form>.
	 * @var array
	 */
	protected $attributes;
	
	/**
	 * Значение аттрибута action тега <form>.
	 * @var string
	 */
	protected $action;

	/**
	 * Значение аттрибута action тега <form>.
	 * @var string
	 */
	protected $method;

	/**
	 * Значение аттрибута action тега <form>.
	 * @var string
	 */
	protected $target;

	/**
	 * Значение аттрибута action тега <form>.
	 * @var string
	 */
	protected $enctype;
	
	/**
	 * Опции отрисовки формы.
	 * @var array
	 */
	protected $options;
	
	/**
	 * Значение аттрибута action тега <form>.
	 * @var string
	 */
	protected $name; // @todo пока не реализовано.

	/**
	 * Имя CSS класса для меток "обязателен для заполнения"
	 * @var string
	 */
	protected $required_class;
	
	/**
	 * Внутренний счетчик ID-шников элементов формы.
	 * @var int;
	 */
	protected static $id_cnt = 0;
	
	/**
	 * Конструктор.
	 * 
	 * @param array $data - массив с данными формы.
	 */
	public function __construct($data = false)
	{
		$this->attributes = array();
		$this->action = '';
		$this->method = 'post';
		$this->enctype = '';
		$this->target = '';
		$this->class = ' class="default-form"';
		$this->required_class = 'required';
		
		if ($data !== false) {
			$this->setFormData($data);
		}
	}

	/**
	 * Отрисовка формы.
	 * @return text
	 */
	public function __tostring()
	{
		ob_start();
		$this->render();
		return ob_get_clean();
	}

	/**
	 * Установить данные формы.
	 *
	 * @param array $data 
	 * @return bool
	 */
	public function setFormData($data)
	{
		// @todo заполнение значений по умолчанию.
		$this->data = $data;
		
		if (isset($data['target'])) {
			$this->setTarget($data['target']);
		}
		
		if (isset($data['enctype'])) {
			$this->setEnctype($data['enctype']);
		}
		
		if (isset($data['action'])) {
			$this->action = $data['action'];
		}
		
		if (isset($data['class'])) {
			$this->class = ' class="' . $data['class'] . '"';
		}
		
		if (!isset($this->data['fieldsets'])) {
			$this->data['fieldsets'] = array();
		}
		
		if (!isset($this->data['hiddens'])) {
			$this->data['hiddens'] = array();
		}
		
		if (!isset($this->data['elements'])) {
			$this->data['elements'] = array();
		}
		
		if (!isset($this->data['buttons'])) {
			$this->data['buttons'] = array();
		}
		
		return true;
	}
	
	/**
	 * Отрисовка формы.
	 * 
	 * @param void
	 * @return void
	 */
	public function render()
	{
		// Если данных формы нет или данные не представляют из себя массив, то возвращается false.
		if (empty($this->data) or !is_array($this->data)) {
			return false;
		}

		// Открытие тега <form>
		echo "\n\n<form action=\"$this->action\" method=\"$this->method\"". $this->enctype . $this->target . $this->class;
		foreach ($this->attributes as $key => $val) {
			echo " $key=\"$val\"";
		}
		echo ">\n";
		
		// Скрытые элементы.
		foreach ($this->data['hiddens'] as $key => $value) {
			echo "<input type=\"hidden\" name=\"$key\" value=\"$value\"/>\n";
		}

		// Сначала отрисовываются филсеты, если они есть.
		foreach ($this->data['fieldsets'] as $fieldset_name => $fieldset_val) {
			// Все поля запаковать в этот филсет.
			if ($fieldset_val['elements'] === 'all') {
				echo "<fieldset id=\"idf-$fieldset_name\"><legend id=\"idf-$fieldset_name-legend\">$fieldset_val[title]</legend>\n";
				foreach ($this->data['elements'] as $key => $value) {
					$this->renderElement($key, $value);
					unset($this->data['elements'][$key]);
				}
				echo "</fieldset>\n";
			} elseif (count($fieldset_val['elements']) > 0) {
				
				echo "<fieldset id=\"idf-$fieldset_name\"><legend id=\"idf-$fieldset_name-legend\">$fieldset_val[title]</legend>\n";
				// Отрисовка элементов перечисленных в филдсете.
				foreach ($fieldset_val['elements'] as $element) {
					if (isset($this->data['elements'][$element])) {
						$this->renderElement($element, $this->data['elements'][$element]);
					}
					unset($this->data['elements'][$element]);
				}
				echo "</fieldset>\n";
			}
		}
		
		// Элементы формы.
		foreach ($this->data['elements'] as $key => $val) {
			$this->renderElement($key, $val);
		}
		
		// Кнопки.
		if (count($this->data['buttons']) > 0) {
			echo "<div class=\"field\">\n";
			foreach ($this->data['buttons'] as $key => $val) {
				$attr = $this->getAttributesStringFromArray($val);
				echo "\t<input name=\"$key\" value=\"$val[value]\"{$attr}/>\n";
			}
			echo "</div>\n";
		}
		
		// Закрытие тега <form>
		echo "</form>\n\n";
	}
	
	/**
	 * Отрисовка элемента.
	 *
	 * @param string $name - имя элемента.
	 * @param array $val - массив с данными.
	 * @return void
	 */
	public function renderElement($key, $val)
	{
		$label = @$val['label']; // @todo сделать нормально ;)
		$value = @$val['value'];
		
		$htmlspecialchars = htmlspecialchars($value);
		
		$id = 'id-' . str_replace(array('[', ']'), '-', $key) . '' . self::$id_cnt++;
		
		echo "<div class=\"row\">\n";
		
		if (strlen($label) > 0) {
			if (isset($val['required']) and $val['required'] == 1) {
				echo "\t<label for=\"$id\" class=\"{$this->required_class}\">$label <em title=\"Обязателен для заполнения\">*</em></label>\n";
			} else{
				echo "\t<label for=\"$id\">$label</label>\n";
			}
			
		}
		
		$attr = $this->getAttributesStringFromArray($val);
		
		echo "\t<div class=\"field\">";

		switch ($val['type']) {
			case 'string':
			case 'text':
				echo "<input name=\"$key\" value=\"$htmlspecialchars\" id=\"$id\"{$attr}/>";
				break;
			case 'pass':
			case 'password':
				echo "<input name=\"$key\" value=\"$value\" id=\"$id\"{$attr}/>"; // @todo зачем тут value=\"$value\"?
				break;
			case 'checkbox':
				// @todo перенести логику чекбокса в метод getAttributesStringFromArray()
				if ($value == 1) {
					$checked = ' checked="checked"';
				} else {
					$checked = '';
				}
				echo "<input type=\"hidden\" name=\"$key\" value=\"0\"/><input name=\"$key\" value=\"1\"$checked id=\"$id\"{$attr}/>";
				break;
			case 'select':
				echo "<select name=\"$key\" id=\"$id\"{$attr}>\n";
				// @todo сделать возможность задавать аттрибуты и стили для options в select
				foreach ($val['options'] as $opkey => $opval) {
					if (isset($val['value']) and (string) $val['value'] === (string) $opkey) {
						$selected = ' selected="selected"';
					} else {
						$selected = '';
					}

					if (is_array($opval)) {
						$disabled = '';
						if (isset($opval['disabled']) and ($opval['disabled'] == true or $opval['disabled'] == 'disabled')) {
							$disabled = ' disabled="disabled"';
						}
						echo "\t<option value=\"$opkey\"$selected$disabled>$opval[title]</option>\n";
					} else {
						echo "\t<option value=\"$opkey\"$selected>$opval</option>\n";
					}
					
				}
				echo "\t</select>";
				break;
			case 'textarea':
				echo "<textarea name=\"$key\" id=\"$id\"{$attr}>$htmlspecialchars</textarea>";
				break;
			case 'file':
				echo "<input name=\"$key\" id=\"$id\"{$attr}/>";
				break;
			case 'html':
				echo "$value";
				break;
			default;
		}

		if (isset($this->data['autofocus']) and $this->data['autofocus'] == $key) {
			echo "\n\t<script type=\"text/javascript\">document.getElementById('" . $id . "').focus();</script>\n";
		}

		echo "</div>\n</div>\n";
	}
	
	/**
	 * Формирование строки аттрибутов для HTML тэга исходя из массива параметров элемента.
	 *
	 * @param array $val
	 * @return string 
	 */
	private function getAttributesStringFromArray($val)
	{
		$attr = '';
		foreach ($val as $key => $value) {
			// Пропуск некоторых значений.
			if ($key == 'label' or
				$key == 'id' or
				$key == 'value' or
				$key == 'options'
				) {
				continue;
			}
			
			if ($key == 'type' and $value == 'select') {
				continue;
			}
			
			if ($key == 'required') {
				if ($value == 1) {
					$attr .= " required";
				}
				continue;
			}
			
			// Для textarea по умолчанию ставятся rows и cols
			if ($key == 'type' and $value == 'textarea') {
				
				if (!isset($val['style'])) {
					$attr .= " style=\"width: 100%;height: 120px;\"";
				}
				
				if (!isset($val['rows'])) {
					$attr .= " rows=\"4\"";
				}
				if (!isset($val['cols'])) {
					$attr .= " cols=\"50\"";
				}
				
				continue;
			}
			
			// Алиас string для text
			if ($key == 'type' and $value == 'string') {
				$attr .= " $key=\"text\"";
				continue;
			}
			
			// Алиас pass для password
			if ($key == 'type' and $value == 'pass') {
				$attr .= " $key=\"password\"";
				continue;
			}
			
			if (($key == 'disabled' or $key == 'readonly' or $key == 'checked') and ($value == 1 or $value == $key)) {
				$attr .= " $key=\"$key\"";
				continue;
			}
			
			if (($key == 'disabled' or $key == 'readonly' or $key == 'checked') and ($value == 0 or $value == false)) {
				continue;
			}
			
			$attr .= " $key=\"$value\"";
		}
		
		return $attr;
	}
	
	/**
	 * Установка аттрибута формы target.
	 * 
	 * @param string
	 *		_blank   Загружает страницу в новое окно браузера. 
	 *		_self    Загружает страницу в текущее окно. (Значение по умолчанию)
	 *		_parent  Загружает страницу во фрейм-родитель, если фреймов нет, то этот параметр работает как _self.
	 *		_top     Отменяет все фреймы и загружает страницу в полном окне браузера, если фреймов нет, то этот параметр работает как _self.
	 * @return void
	 */
	public function setTarget($t)
	{
		switch ($t) {
			case '_parent':
			case 'parent':
				$this->target = " target=\"_parent\"";
				break;
			case '_blank':
			case 'blank':
				$this->target = " target=\"_blank\"";
				break;
			case '_self':
			case 'self':
				$this->target = " target=\"_self\"";
				break;
			case '_top':
			case 'top':
				$this->target = " target=\"_top\"";
				break;
			default:
		}
	}

	/**
	 * Установка аттрибута формы enctype.
	 * 
	 * @param string
	 *		file
	 *		multipart/form-data
	 *		mail
	 * @return void
	 */
	public function setEnctype($type)
	{
		switch ($type) {
			case 'file':
			case 'multipart/form-data':
				$this->enctype = " enctype=\"multipart/form-data\"";
				break;
			case 'mail':
				$this->enctype = " enctype=\"text/plain\"";
				break;
			default:
		}
	}
}