<?php
/**
 * Реализация некоторых функций, для совместимости с PHP 5.2
 * 
 * @author	Artem Ryzhkov
 * @package	Kernel
 * @license	http://opensource.org/licenses/gpl-2.0
 * 
 * @version 2011-12-25.0
 */
 
if(!function_exists('get_called_class')) {
	function get_called_class() {
		$obj = false;
		$backtrace = debug_backtrace();
		foreach($backtrace as $row) {
			if($row['function'] == 'call_user_func' and isset($backtrace[2]['args'][0])) {	
				$obj = explode('::', $backtrace[2]['args'][0]);
				$obj = $obj[0];
				break;
			}
		}
 
		if(!$obj) {			
			$backtrace = $backtrace[1];
			$file = file_get_contents($backtrace['file']);
			$file = explode("\n", $file);
			for($line = $backtrace['line'] - 1; $line > 0; $line--) {
				preg_match("/(?<class>\w+)::(.*)/", trim($file[$line]), $matches);
				if (isset($matches['class'])) {
					return $matches['class'];
				} 
			}
			throw new Exception('Could not find');
		}
		
		return $obj;
	}
}