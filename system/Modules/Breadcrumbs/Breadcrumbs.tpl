<?php
$cnt = count($data['items']);
if ($cnt > 0) {
	foreach ($data['items'] as $item) {
		echo --$cnt ? "<a href=\"{$item['uri']}\" title=\"{$item['descr']}\">" : '';
		echo $item['title'];
		echo $cnt ? "</a>&nbsp;{$data['delimiter']}&nbsp;" : '';
	}	
}