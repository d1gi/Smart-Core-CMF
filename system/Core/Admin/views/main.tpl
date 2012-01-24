<?php
/**
 * @version 2012-01-24.0
 */	
?>

<style type="text/css">
ul.admin-list li {
	background: url("<?php echo HTTP_SYS_RESOURCES?>images/list-item.png") no-repeat scroll 0 11px transparent;
}
</style>

<?php if (!cf_is_get('popup')):?>
<div id="breadcrumbs">
	<?php echo $this->Breadcrumbs?>
</div>
<?php endif?>


<?php
// Меню текущего раздела
if (!empty($this->current_menu)) {
	$breadcrumbs = $this->Breadcrumbs->get();
	
	echo "<ul class=\"admin-list\">\n";
	foreach ($this->current_menu as $key => $value) {
		echo "\t<li class=\"leaf\">\n";
		
		echo "\t\t<a href=\"" . $breadcrumbs[count($breadcrumbs) - 1]['uri'];
		
		if (isset($value['path'])) {
			echo "$value[path]/\">";
		} else {
			echo "$key/\">";
		}
		
		echo "$value[title]</a>\n";
		
		if (isset($value['descr'])) {
			echo "\t\t<div class=\"description\">$value[descr]</div>\n";
		}
		
		echo "\t</li>\n";
	}
	echo "</ul>\n";
}


if ($this->welcome) {
	echo "<hr />Вы вошли как: <b><a href=\"" . HTTP_ROOT . ADMIN . "/users/edit/{$this->welcome['user_id']}/\" title=\"Редактировать учетную запись\">{$this->welcome['nickname']}</a></b>";
	?>
	<form action="<?php echo HTTP_ROOT . ADMIN?>/" method="post" style="display: inline;" target="_parent">
	<input type="submit" name="submit[user_logout]" value="Выход">
	</form>
	<?php
}

echo $this->Navigation;
echo $this->Content;
