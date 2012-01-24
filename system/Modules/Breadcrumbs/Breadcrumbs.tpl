<?php

if (!empty($this->items)) {
	$bc = Registry::get('Breadcrumbs')->get();
	$cnt = count($bc);
	if ($cnt > 0) {	
		foreach ($bc as $item) {
			echo --$cnt ? "<a href=\"{$item['uri']}\" title=\"{$item['descr']}\">" : '';
			echo $item['title'];
			echo $cnt ? "</a>&nbsp;{$this->delimiter}&nbsp;" : '';
		}	
	}
}

