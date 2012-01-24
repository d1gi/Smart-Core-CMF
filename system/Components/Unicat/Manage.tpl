<!--<h1>Управление каталогом</h1>-->

<ul>
<li><a href="?items">Записи</a></li>
<li><a href="?properties">Свойства</a></li>
<li><a href="?structures">Cтруктуры</a>
	<?php
	if (!empty($this->structures_list)) {
		echo "<ul>\n";
		foreach ($this->structures_list as $key => $value) {
			echo "\t<li><a href=\"?structure=$value[id]\">$value[name] ($value[descr])</a></li>\n";
		}
		echo "</ul>\n";
	}
	?>
</li>
</ul>

<div class="default-form">

<table width="100%">
	<tr>
		<td>

		</td>
		<td>
			
		</td>
	</tr>
</table>

</div>

<form action="../../" target="_parent" method="post">
	<div class="field"><input type="submit" name="close" value="Закрыть" /></div>
</form>
