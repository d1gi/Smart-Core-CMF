<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Интерфейс для всех модулей.
 * 
 */
interface ModuleInterface
{
	/**
	 * Метод run() необходимо описывать в классах модулей.
	 * 
	 * @access public
	 * @return void
	 */
	public function run($parser_data);
}
