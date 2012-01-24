<?php

// @todo сделать валидную генерацию списков XTHML

$level = 0;

echo $this->current_folder;

$Form = new Helper_Form($this->form);
echo $Form;

if ($this->list) {
	foreach ($this->list as $key => $value) {
		// Если ключи числовые, то считаем, что надо отобразить список нод.
		if (is_numeric($key)) {
			echo "<ul>\n";                                                    
			foreach ($this->list as $key => $value) {
				
				$edit_href = "<a href=\"" . HTTP_ROOT . ADMIN . '/structure/node/' . $key . "/\" >" . $value['descr'] . ' (' .  $value['module_id'] . ')</a>';
				
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

