<?php
/**
 * Отрисовка форм для шаблонизатора.
 * 
 * @description вывод данных идет по ходу вызова методов, но можно будет добавить возможность 
 * по ходу вызова методов, собирать массив с данными для отрисовки формы, а по завершению 
 * отрисовывать всё одним заходом, так сделано в Zend_Form.
 * 
 */

class Helper_Form_Old
{
	protected $attribs;
	protected $action;
	protected $method;
	protected $target;
	protected $enctype;
	protected $name; // @todo пока не реализовано.


	public function __construct()
	{
		$this->attribs = array();
		$this->action = '';
		$this->method = 'post';
		$this->enctype = '';
		$this->target = '';
	}


	/**
	 * Инструмент строящий массив для генерации текстового поля. 
	 */
	public function toolText($name, $value, $maxlength = false, $title = false, $id = false, $disabled = false)
	{
		$tmp = array();
		$tmp['title'] = $title;
		$tmp['type'] = 'text';
		if ($disabled === false) {
			$tmp['disabled'] = 0;
		} else {
			$tmp['disabled'] = 1;
		}
		$tmp['enabled'] = 'text';
		$tmp['name'] = $name;
		$tmp['value'] = $value;
		if ($id === false) {
			$tmp['id'] = md5($name . microtime(1));
		} else {
			$tmp['id'] = $id;
		}
		if ($maxlength !== false) {
			$tmp['maxlength'] = $maxlength;
		}
		else {
			$tmp['maxlength'] = false;
		}
		return $tmp;
	}


	public function toolCheckbox($name, $value, $title = false, $id = false)
	{
		$tmp = array();
		$tmp['title'] = $title;
		$tmp['type'] = 'checkbox';
		$tmp['value'] = $value;
		$tmp['name'] = $name;
		if ($id === false) {
			$tmp['id'] = md5($name . microtime(1));
		} else {
			$tmp['id'] = $id;
		}
		return $tmp;
	}


	public function toolSelect($name, $value, $title = false, $id = false)
	{
		$tmp = array();
		$tmp['title'] = $title;
		$tmp['type'] = 'select';
		$tmp['value'] = $value;
		$tmp['name'] = $name;
		if ($id === false) {
			$tmp['id'] = md5($name . microtime(1));
		} else {
			$tmp['id'] = $id;
		}
		return $tmp;
	}


	public function toolSubmit($name, $value, $title = false, $id = false)
	{
		$tmp = array();
		$tmp['title'] = $title;
		$tmp['type'] = 'submit';
		$tmp['value'] = $value;
		$tmp['name'] = $name;
		if ($id === false) {
			$tmp['id'] = md5($name . microtime(1));
		} else {
			$tmp['id'] = $id;
		}
		return $tmp;
	}


	public function setAction($a)
	{
		$this->action = $a;
	}


	public function setAttrib($key, $val)
	{
		$this->attribs[$key] = $val;
	}
	

	public function setMethod($a)
	{
		switch ($a) {
			case 'post':
				$this->method = 'post';
				break;
			case 'get':
				$this->method = 'get';
				break;
			default:
		}
		
	}


	public function setEnctype($type)
	{
		switch ($type) {
			case 'file':
				$this->enctype = " enctype=\"multipart/form-data\"";
				break;
			case 'mail':
				$this->enctype = " enctype=\"text/plain\"";
				break;
			
			default:
		}
	}


	/**
	 * _blank  Загружает страницу в новое окно браузера. 
	 * _self   Загружает страницу в текущее окно. (Значение по умолчанию)
	 * _parent Загружает страницу во фрейм-родитель, если фреймов нет, то этот параметр работает как _self.
	 * _top    Отменяет все фреймы и загружает страницу в полном окне браузера, если фреймов нет, то этот параметр работает как _self.
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
	 * Открытие формы.
	 */
	public function open()
	{
		echo "\n\n<form action=\"$this->action\" method=\"$this->method\"$this->enctype" . $this->target;
		foreach ($this->attribs as $key => $val) {
			echo " $key=\"$val\"";
		}
		echo ">\n";
	}
	
	
	/**
	 * Закрытие формы.
	 */
	public function close()
	{
		$this->__construct(); // @todo некрасиво... думаю всёже лучше накапливать данные формы прямо в классе, а потом рендерить её целиком, как в Zend_Form
		echo "</form>\n\n";
	}

	public function inputText($name, $value = "", $label = false, $class = false, $extra = false)
	{
		
		/*
		if ($class !== false) {
			echo "<div class=\"$class\">\n";
		} else {
			echo "<div>\n";
		}
		
		if ($label !== false) {
			echo "  <div class=\"title\">$label</div>\n";
		}
		
		// @todo maxlength
		echo "  <div class=\"field\"><input type=\"text\" name=\"$name\" value=\"$value\" maxlength=\"200\" /></div>\n</div>\n";
		*/
	}


	/**
	 * Отобразить поле ввода текста из массива 
	 */
	public function inputTextArr($arr, $class = false, $extra = false) // @todo что за $extra? :)
	{
		// стартуем открывающий блок.
		if ($class !== false) {
			echo "<div class=\"$class\">\n";
		} else {
			echo "<div>\n";
		}
		
		// заголовок поля.
		if ($arr['title'] !== false) {
			echo "  <div class=\"title\"><label for=\"$arr[id]\">$arr[title]</label></div>\n";
//			echo "  <div class=\"title\">$arr[title]</div>\n";
		}
		
		if ($arr['maxlength'] == false) {
			$maxlength = '';
		} else {
			$maxlength = " maxlength=\"$arr[maxlength]\"";
		}

		if ($arr['disabled'] == 1) {
			$disabled = ' disabled="disabled"';
		} else {
			$disabled = '';
		}

		echo "  <div class=\"field\"><input type=\"text\" name=\"$arr[name]\" id=\"$arr[id]\" value=\"$arr[value]\"$maxlength $disabled /></div>";
		
		// закрывающий тег		
		echo "\n</div>\n";
	}
	
	/**
	 *  
	 * $confirm - гененирует яваскриптовый алерт "а вы уверены, что ходите сделать действие $confirm"
	 */
	public function inputSubmitArr($arr, $class = false, $confirm = false)
	{
		
		if ($confirm != false) {
			$confirm = " onClick=\"return confirm('$confirm')\"";
		} else {
			$confirm = '';
		}
		// стартуем открывающий блок.
		if ($class !== false) {
			echo "<div class=\"$class\">\n";
		} else {
			echo "<div>\n";
		}
		
		if ($arr['title'] !== false) {
			echo "  <div class=\"title\">$arr[title]</div>\n";
		}
		
		echo "  <div class=\"field\"><input type=\"submit\" name=\"$arr[name]\" value=\"$arr[value]\"$confirm /></div>";

		// закрывающий тег		
		echo "\n</div>\n";
	}


	public function inputSubmit($name, $value, $title = false)
	{
		/*
		if ($title !== false) {
			echo "<div class=\"title\">$title</div>";
		}
		
		echo "  <input type=\"submit\" class=\"submit\" name=\"$name\" value=\"$value\" />\n\n";
		*/
	}
		

	public function inputHidden($name, $value)
	{
		echo "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
	}

	public function inputHiddenArr($arr)
	{
		echo "<input type=\"hidden\" name=\"$arr[name]\" value=\"$arr[value]\" />\n";
	}


	public function inputCheckbox($name, $value, $title = false, $class = false)
	{
		if ($class != false) {
			echo "<div class=\"$class\">";
		} else {
			echo "<div>";
		}
		
		if ($title != false)
			echo "<div class=\"title\">$title</div>";
		
		echo "<div class=\"field\"><input type=\"checkbox\" class=\"submit\" name=\"$name\" $value /></div></div>\n\n";
		
	}


	public function inputCheckboxArr($arr, $class = false)
	{
		if ($class != false) {
			echo "<div class=\"$class\">\n";
		} else {
			echo "<div>\n";
		}
		
		if ($arr['title'] !== false) {
			echo "  <div class=\"title\"><label for=\"$arr[id]\">$arr[title]</label></div>\n";
		}
		
		if ($arr['value'] == 1) {
			$value = ' checked="checked"';
		} else {
			$value = '';
		}
		
		echo "  <div class=\"field\"><input type=\"checkbox\" id=\"$arr[id]\" name=\"$arr[name]\"$value /></div>\n</div>\n";
	}


	public function select($name, $select, $title = false)
	{
		echo "<div>";
		
		if ($title != false) {
			echo "<div class=\"title\">$title</div>";
		}
		
		echo "<div class=\"field\">
			<select name=\"$name\">";
		
		foreach ($select as $key => $val) {
			$level = "";

			if(isset($val['level'])) {
				while (--$val['level'] >= 0) {
					$level .= "&nbsp;&nbsp;&nbsp;";
				}
			}
			
			echo "<option value=\"$key\" $val[selected]>{$level}$val[title]</option>\n";
		}
		
		echo "</select></div></div>\n\n";
	}

	
	public function selectArr($select, $title = false)
	{
		echo "<div>\n";
		
		if ($select['title'] != false) {
			echo "  <div class=\"title\"><label for=\"$select[id]\">$select[title]</label></div>\n";
		}
		
		echo "  <div class=\"field\">\n  <select name=\"$select[name]\" id=\"$select[id]\">\n";
		
		foreach ($select['value'] as $key => $val) {
			$level = '';
			if ($val['selected'] == 1) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			if(isset($val['level'])) {
				while (--$val['level'] >= 0) {
//					$level .= '&#xb7;&#xb7;';
					$level .= '&nbsp;&nbsp;';
				}
			}
			echo "  <option value=\"$key\"$selected>{$level}$val[title]</option>\n";
		}
		echo "  </select>\n  </div>\n</div>\n";
	}
	
		
}