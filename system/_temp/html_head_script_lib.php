<?php 

// Подключение запрошенных библиотек.

$ScriptsLib = Registry::get('ScriptsLib');		
foreach ($ScriptsLib->get() as $name => $value) {
	if (isset($value['js'])) {
		foreach ($value['js'] as $val) {
			$head .= "\t<script";												
			$head .= ' type="text/javascript" src="' . $val . '">';
			$head .= "</script>\n";					
		}
	}
	
	if (isset($value['css'])) {
		foreach ($value['css'] as $val) {
			$head .= "\t<style";
			$head .= ' type="text/css"> @import "' . $val . '"; ';
			$head .= "</style>\n";
		}
	}
}
