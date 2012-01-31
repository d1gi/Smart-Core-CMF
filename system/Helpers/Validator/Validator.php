<?php 
/**
 * Хелпер валидации.
 * 
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://www.opensource.org/licenses/gpl-2.0
 * 
 * @version 2011-11-13.1
 */
class Helper_Validator
{
	/**
	 * Объект валидатора.
	 * @var object
	 */
	private $Validator;
	
	/**
	 * Сообщения об ошибках.
	 */
	private $messages;
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$Translator = new Zend_Translate(
			'array',
			DIR_ZEND_FRAMEWORK . '/resources/languages/',
			'ru',
			array('scan' => Zend_Translate::LOCALE_DIRECTORY)
			);
		Zend_Validate_Abstract::setDefaultTranslator($Translator);
	}
	
	/**
	 * NewFunction
	 *
	 * @param
	 * @return bool
	 */
	public function isValid($value, $validator, $params)
	{
		switch ($validator) {
			case 'email':
				$this->Validator = new Zend_Validate_EmailAddress();
				break;
			case 'url':
			case 'uri':
				if (!Zend_Uri::check($value)) {
					$this->messages = array('Некорректный URL');
					return false;
				}
				return true;
				break;
			default;
				$Validator_Class = 'Zend_Validate_' . $validator;
				$this->Validator = new $Validator_Class();
		}
		
		if ($this->Validator->isValid($value)) {
			return true;
		} else {
			$this->messages = $this->Validator->getMessages();
			return false;
		}
	}
	
	/**
	 * Валидация e-mail.
	 *
	 * @param string $email
	 * @return string
	 */
	public function email($email)
	{
		$this->Validator = new Zend_Validate_EmailAddress();
		if ($this->Validator->isValid($email)) {
			return true;
		} else {
			$this->messages = $this->Validator->getMessages();
			return false;
		}
	}
	
	/**
	 * Получить сообщения об ошибках.
	 *
	 * @param void
	 * @return array
	 */
	public function getMessages()
	{
		return $this->messages;
	}
}