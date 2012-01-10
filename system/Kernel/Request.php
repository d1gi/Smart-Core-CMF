<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Входные данные.
 * 
 * @todo пока не юзается...
 */
class Request extends Singleton
{
	public static $post;
	public static $get;

	/**
	 * Constructor.
	 *
	 * @param
	 * @return
	 */
	protected function __construct()
	{
		if (count($_POST) > 0) {
			self::$post = &$_POST;
		} else {
			self::$post = false;
		}	
	}
}