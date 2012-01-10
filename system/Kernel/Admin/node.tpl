<?php

// @todo сделать валидную генерацию списков XTHML

$level = 0;

echo @$data['current_folder'];

$Form = new Helper_Form(@$data['form']);
echo $Form;

if (isset($data['list'])) {
	foreach ($data['list'] as $key => $value) {
		// Если ключи числовые, то считаем, что надо отобразить список нод.
		if (is_numeric($key)) {
			echo "<ul>\n";                                                    
			foreach ($data['list'] as $key => $value) {
				
				if (empty($data['current_folder'])) {
					$edit_href = "<a href=\"" . $this->EE->breadcrumbs[count($this->EE->breadcrumbs) - 1]['uri'] . $key . "/\" >" . $value['descr'] . ' (' .  $value['module_id'] . ')</a>';
				} else {
					$edit_href = "<a href=\"" . $this->EE->breadcrumbs[count($this->EE->breadcrumbs) - 2]['uri'] . $key . "/\" >" . $value['descr'] . ' (' .  $value['module_id'] . ')</a>';
				}
				
				if ($value['is_active'] == 0) {
					$edit_href = "<span style=\"text-decoration: line-through;\">$edit_href</span>";
				}
				
				echo "<li>$edit_href</li>\n";
				
			}
			echo "</ul>\n\n";
			break;
		} else {
			break;
		}
	}	
}

