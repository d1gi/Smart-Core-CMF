<script type="text/javascript">
function OpenFile(fileUrl)
{
/*
	window.top.opener.SetUrl( encodeURI( fileUrl ).replace( '#', '%23' ) ) ;
	window.top.close() ;
	window.top.opener.focus() ;
*/
	window.top.opener.tinyfck.document.forms[0].elements[window.top.opener.tinyfck_field].value = fileUrl;
	if (window.top.opener.tinyfck.document.forms[0].elements[window.top.opener.tinyfck_field].onchange != null) {
		window.top.opener.tinyfck.document.forms[0].elements[window.top.opener.tinyfck_field].onchange();
	}
	window.top.close();
	window.top.opener.tinyfck.focus();
	
}
</script>

<?php
/*
<div id="filemanager_sidebar" >
	<b>Коллекции</b>
	<br /><br />
	<ul>
<?php
	foreach ($data['collections_list'] as $key => $value) {
		echo "<li><a href=\"" . HTTP_ROOT . "filemanager/$value[name]/\">$value[descr]</a> <ul>";
		foreach ($value['categories'] as $key2 => $value2) {
			echo "<li><a href=\"" . HTTP_ROOT . "filemanager/$value[name]/$key2/\">$value2</a> </li>";
		}
		echo "</ul>
		</li>";
		// <li>Новости</li>
	}
?>
	</ul>
</div>

*/
?>
<div id="filemanager_container">
	<?php
	$Form = new Helper_Form($data['upload_form']);
	echo $Form;
	?>
	<div class="filemanager_order_by">
		Упорядочить по:
		<a href="?sort_name=<?php echo (@$_GET['sort_name'] == 'asc')? 'desc' : 'asc';?>">имени</a>
		<a href="?sort_date=<?php echo (@$_GET['sort_date'] == 'asc')? 'desc' : 'asc';?>">дате</a>
		<a href="?sort_size=<?php echo (@$_GET['sort_size'] == 'asc')? 'desc' : 'asc';?>">размеру</a></div>
	<div class="clear"></div>
	<?php
	foreach ($data['file_list'] as $key => $value) {
		echo "\t<div class=\"filemanager_file_entry\" onclick=\"OpenFile('$value[uri]');return false;\">\n\t<div class=\"filemanager_file_thumb\">";
		echo "\t<img src=\"$value[thumb]\" alt=\"\" title=\"Дата загрузки: $value[upload_datetime], Размер $value[size]\" />\n";
		echo "\t</div><div class=\"filemanager_file_name\">$value[original_filename]</div></div>\n";
	}
	?>
</div>

<div class="clear"></div>
