
<table class="order">
<form action="" method="post">
	<input type="hidden" name="node_id" value="20">
	<tr>   
		<th width="120">Фото</th>
		<th>Артикул</th>
		<th>Упаковка</th>
		<th>Кол-во, пар</th>
		<th>Цена, за пару</th>
		<th>Цена, всего</th>
		<th>Действие</th>
	</tr>

<?php

$order_price = 0;

foreach ($data['basket'] as $key => $value) {
	$order_price += $value['cnt'] * $value['price'];
	echo "<tr>
		<td><a href=\"$value[link]\" target=\"_blank\" title=\"Откроется в новом окне\"><img src=\"$value[img]\" width=\"120\" /></a></td>
		<td>$value[articul]</td>
		<td>$value[packing]</td>
		<td><input type=\"text\" name=\"pd[$key]\" value=\"$value[cnt]\" /></td>
		<td><b>$value[price]</b> руб.</td>
		<td><b>" . $value['price'] * $value['cnt'] . "</b> руб.</td>
		<td><a href=\"?del_item=$key\" onclick=\"return confirm('Вы уверены, что хотите удалить товар из корзины?')\",>Удалить</a></td>
		</tr>
		";
}

?> 

</table>
<br /><input type="submit" name="submit[recalculate]" value="Пересчитать кол-во упаковок" <?php if ($order_price == 0) { echo 'disabled="disabled"'; } ?>>
</form>


<br /><br />
<p>Стоимость Вашего заказа: <b><?php echo $order_price; ?></b> рублей. 

<?php
	if ($order_price != 0) {
		echo '<u><a href="?action=order">Оформить заказ</a></u>';
	}
?>

</p>

<br />
<a href="history/">История заказов</a>
