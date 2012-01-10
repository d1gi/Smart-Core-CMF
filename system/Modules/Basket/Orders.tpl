<?php
if (isset($_GET['id']) and is_numeric($_GET['id'])) {
	$id = $_GET['id'];
	echo "<h1>Заказ №$id</h1><br /><br /><br />";
	
	$data = str_replace("\n", "<br />", $data['orders_list'][$id]['data']);
//		$text = preg_replace("#(https?|ftp)://\S+[^\s.,> )\];'\"!?]#",'<a href="\\0" target="_blank">\\0</a>',$data);
	$text = preg_replace("(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)",'<a href="\\0" target="_blank">\\0</a>',$data);
	echo $text . " <br /><br /><a href=\"?\"><< Назад к списку всех заказов</a>";
	
	
} else {
	?>
	<h1>Все заказы</h1>

	<table class="order">
		<tr>   
			<?php
			if ($data['admin_mode']) {
				echo '<th width="20">№</th>';
				echo '<th width="120">Дата</th>';
				echo '<th>Информация о клиенте</th>';
				echo '<th>Сумма</th>';
			} else {
				echo '<th width="120">Дата</th>';
				echo '<th>Заказ</th>';
			}
			?>
		</tr>
	<?php
	$order_price = 0;

	foreach ($data['orders_list'] as $key => $value) {
	//	$order_price += $value['cnt'] * $value['price'];
		if ($data['admin_mode']) {
			echo "<tr>
				<td style=\"vertical-align: top;\">$key</td>
				<td style=\"vertical-align: top;\">$value[datetime]</td>
				<td><a href=\"?id=$key\">" . str_replace("\n", "<br />", $value['client_data']) . "</a></td>
				<td style=\"vertical-align: top;\">$value[amount] руб.</td>
				</tr>
				";
		} else {
			echo "<tr>
				<td style=\"vertical-align: top;\">$value[datetime]</td>
				<td><a href=\"?id=$key\">" . str_replace("\n", "<br />", $value['data']) . "</a></td>
				</tr>
				";
		}
	
	}
	echo "</table>";
	
	
	echo "<br /><br /><div class=\"paginator\">\n";
	echo @$data['pages'];
	echo "</div>";
	
}
