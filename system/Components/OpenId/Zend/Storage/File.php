<?php

/**
 * 
 */
class Component_OpenId_Zend_Storage_File extends Zend_OpenId_Consumer_Storage_File
{
	/**
	 * Constructs storage object and creates storage directory
	 *
	 * @param string $dir directory name to store data files in
	 * @throws Zend_OpenId_Exception
	 */
	public function __construct($dir = null) 
	{
		if ($dir !== null) {
			$user = get_current_user();
			if (is_string($user) && !empty($user)) {
				$tmp .= '/' . $user;
			}
			$dir .= $tmp . '/openid/consumer';
		}
		
		parent::__construct($dir);
	}
}