<?php
/**
 * @version 2011-09-27.0
 */	
?>

<style type="text/css">
ul.admin-list li {
	background: url("<?php echo HTTP_SYS_RESOURCES?>images/list-item.png") no-repeat scroll 0 11px transparent;
}
</style>
<?php

// Меню текущего раздела
if (isset($data['current_menu']) and $data['current_menu'] != false and count($data['current_menu']) != 0) {
	echo "<ul class=\"admin-list\">\n";
	foreach ($data['current_menu'] as $key => $value) {
		echo "\t<li class=\"leaf\">\n";
		
		if (isset($value['path'])) {
			echo "\t\t<a href=\"" . $this->EE->breadcrumbs[count($this->EE->breadcrumbs) - 1]['uri'] . "$value[path]/\">$value[title]</a>\n";
		} else {
			echo "\t\t<a href=\"" . $this->EE->breadcrumbs[count($this->EE->breadcrumbs) - 1]['uri'] . "$key/\">$value[title]</a>\n";
		}
		
		if (isset($value['descr'])) {
			echo "\t\t<div class=\"description\">$value[descr]</div>\n";
		}
		echo "\t</li>\n";
	}
	echo "</ul>\n";
}


if (isset($data['welcome'])) {
	// <div style="float: right;">
	echo "<hr />Вы вошли как: <b><a href=\"" . HTTP_ROOT . ADMIN . "/users/edit/{$data['welcome']['user_id']}/\" title=\"Редактировать учетную запись\">{$data['welcome']['nickname']}</a></b>";
	?>
	<form action="<?php echo HTTP_ROOT . ADMIN?>/" method="post" style="display: inline;" target="_parent">
	<input type="submit" name="submit[user_logout]" value="Выход">
	</form>
	<?php
}
