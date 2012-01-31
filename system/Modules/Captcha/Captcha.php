<?php
/**
 * Module CAPTCHA.
 * 
 * @uses Folder
 * @uses Module_Captcha_Kcaptcha_Kcaptcha
 * @uses Response
 * @uses Session
 * 
 * @package Module
 * @version 2011-09-21.0
 */
class Module_Captcha extends Module
{
	/**
	 * Размеры изображения
	 * @var int
	 */
	protected $img_width;
	protected $img_height;
	
	/**
	 * Конструктор.
	 */
	protected function init()
	{
		$this->img_width	= 150;
		$this->img_height	= 50;
		$this->Session->start();
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
			case 'getHtmlCode':
				return '<img src="' . Folder::getUri($this->Node->folder_id) . '?' . microtime(1) . '" alt="" width="' . $this->img_width . '" height="' . $this->img_height . '" />';
				break;
			case 'getKeyString':
				$this->Session->start();
				return $this->Session->captcha_keystring;
				break;
			default;
		}
		return true;
	}
	
	/**
	 * Запуск модуля.
	 */
	public function run($params)
	{
		//$Captcha = new Module_Captcha_Kcaptcha2_Kcaptcha2();
		$Captcha = new Module_Captcha_Kcaptcha_Kcaptcha();

		$this->Response->setCompressLevel(0);
		
//		$this->Response->addHeader('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		header('Cache-Control: post-check=0, pre-check=0', false);
		
		ob_start();
		
		if(function_exists("imagejpeg")) {
			$this->Response->setMimeType('image/jpeg');
			imagejpeg($Captcha->getImage(), null, 90);
		} elseif(function_exists("imagegif")) {
			$this->Response->setMimeType('image/gif');
			imagegif($Captcha->getImage());
		} elseif(function_exists("imagepng")) {
			$this->Response->setMimeType('image/x-png');
			imagepng($Captcha->getImage());
		}

		$this->Response->setDirectData(ob_get_clean());
		$this->Session->captcha_keystring = $Captcha->getKeyString();
	}
}