
<div class="default-form">
<?php
$Form = new Helper_Form($this->edit_item_form_data);
echo $Form;


if (is_array($this->list)) {
	echo "<fieldset><legend>Пункты меню</legend>\n<ul>\n";
	
	$level = 0;
	$first_child = true;
	
	foreach ($this->list as $key => $value) {
		$anchor = "<a href=\"" . $this->link . $key . "/\" title=\"" . $value['uri'] . "\">" . $value['title'] . " (id: $key, pos: " . $value['pos'] . ")</a>";

		if ($value['is_active'] == 0) {
			$anchor = "<span style=\"text-decoration: line-through;\">$anchor</span>";
		}
		
		$tab = '';
		for ($i = 0; $i <= $level; $i++) {
			$tab .= "\t";
		}
		
		if ($level == $value['level']) {
			if ($first_child) {
				$first_child = false;
				echo "$tab<li>$anchor";
			} else {
				echo "</li>\n$tab<li>$anchor";
			}
		} elseif ($level < $value['level']) { // 
			$level = $value['level'];
			echo "\n$tab<ul>\n$tab<li>$anchor";
		} else {
			$cnt = $level - $value['level'];
			while ($cnt--) {
				echo "$tab\t</li>\n$tab\t</ul>\n";
			}
			echo "$tab</li>\n$tab<li>$anchor";
			$level = $value['level'];
		}
		
	}
	echo "\n</li>\n</ul>\n\n";

	?>
	<form action="../../" target="_parent" method="post">
		<div class="field"><input type="submit" name="close" value="Закрыть" /></div>
	</form>
	</fieldset>
	
	<fieldset><legend>Добавить новый пункт меню</legend>
	<?php $Form = new Helper_Form($this->new_item_form_data); echo $Form;?>
	</fieldset>
	
	<?php
}
?>

</div>
