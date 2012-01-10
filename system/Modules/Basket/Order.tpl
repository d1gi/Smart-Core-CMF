

<br /><br /><br />
Ваш заказ:
<table class="order">
	<tr>   
		<th width="120">Фото</th>
		<th>Артикул</th>
		<th>Упаковка</th>
		<th>Кол-во, пар</th>
		<th>Сумма</th>
	</tr>

<?php
$order_price = 0;

foreach ($data['basket'] as $key => $value) {
		$order_price += $value['cnt'] * $value['price'];
	echo "<tr>
		<td><a href=\"$value[link]\" target=\"_blank\" title=\"Откроется в новом окне\"><img src=\"$value[img]\" width=\"120\" /></a></td>
		<td>$value[articul]</td>
		<td>$value[packing]</td>
		<td>$value[cnt]</td>
		<td><b>" . $value['price'] * $value['cnt'] . "</b> руб.</td>
		</tr>
		";
}
?> 

</table>

<table class="design" width="100%">
	<tr>
		<td align="right"><p>Итого на сумму: <b><?php echo $order_price; ?></b> рублей.</p></td>
	</tr>
</table>



<?php

$Form = new Helper_Form($data['customer_form']);
echo $Form;
?>

<br /><br />
<a href="/basket/">Вернуться к редактированию заказа</a>
