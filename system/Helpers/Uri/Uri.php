<?php
/**
 * Хелпер для обработки частей запроса URI.
 * 
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://www.opensource.org/licenses/gpl-2.0
 * 
 * @uses Helper_Translit
 * 
 * @version 2011-07-06.1
 */
class Helper_Uri
{
	/**
	 * Подготовка строки к валидному формату части УРИ.
	 *
	 * @param string $str
	 * @return string
	 */
	public function preparePart($str)
	{
		$Translit = new Helper_Translit();
		
		// Удаление служебных символов.
		$symbols = array('(', ')', '{', '}', '[', ']', "'", '"', '!', '#', '^', '*', '$', '%', '&', '?', '/', '\\', '|', '<', '>', ':', ';');
		$str = str_replace(' ', '_', $str);
		$str = strtolower($Translit->rus(trim(str_replace($symbols, '', $str))));		
		return $str;
	}
}