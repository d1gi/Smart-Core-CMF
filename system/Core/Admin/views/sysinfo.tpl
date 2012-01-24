<?php

if (!empty($this->php_settings)) {
	?> 
	<br /><br /><table class="admin-table" width="100%">
		<tr>
			<th>Setting</th>
			<th>Value</th>
			<th>Required</th>
			<th>Recomended</th>
		</tr>
	<?php
	foreach ($this->php_settings as $key => $value) {
		echo "<tr>";
		echo "<td>$value[title]</td>";
		echo "<td>$value[value]</td>";
		echo "<td>$value[required]</td>";
		echo "<td>$value[recomended]</td>";
		echo "</tr>\n";
	} 	
	echo "</table>";
}

if (!empty($this->platform)) {
	?> 
	<br /><br /><table class="admin-table" width="100%">
		<tr>
			<th>Setting</th>
			<th>Value</th>
			<th>Required</th>
			<th>Recomended</th>
		</tr>
	<?php
	foreach ($this->platform as $key => $value) {
		echo "<tr>";
		echo "<td>$value[title]</td>";
		echo "<td>$value[value]</td>";
		echo "<td>$value[required]</td>";
		echo "<td>$value[recomended]</td>";
		echo "</tr>\n";
	} 	
	echo "</table>";
}

if (!empty($this->phpinfo)) {
	echo "<br /><br />" . $this->phpinfo;
	?>
	<style type="text/css">
	body, td, th, h1, h2 {font-family: "Lucida Grande", "Lucida Sans Unicode", Helvetica, sans-serif;}
	td, th {font-size: 80%;}
	table {width: 80%; min-width: 800px;}
	.v {background-color: #F0F0F0;}
	.e {background-color: #ddddff;width: 20%;min-width: 250px;}
	</style>
	<?php
}