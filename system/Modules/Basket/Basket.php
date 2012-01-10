<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Module Basket.
 * 
 * @uses 
 * 
 * @package Module
 * @version 2011-00-00.0
 * 
 * @todo модуль не актуален!!! переделать.
 */
class Module_Basket extends Module
{
	/**
	 * Some value...
	 */
	protected $Catalog;
	
	/**
	 * Кол-во наименований товаров.
	 */
	protected $cnt;
	
	/**
	 * 
	 */
	protected $email;
	
	protected $admin_mode;
	
	/**
	 * Конструктор.
	 * 
	 * @return void
	 */
	protected function init()
	{
		$this->mode = $this->Node->params['mode'];
		$this->email = $this->Node->params['email'];
		$this->admin_mode = $this->Node->params['admin_mode'];
//		$this->Catalog = new Component_Unicat($this->entity_id, $this->Node->id, $this->DB);
		//$this->Catalog = new Component_Unicat(1, $this->Node->id, $this->DB);
		
		$this->__unicat_params = array(
			'entity_id' => 1,
			//'node_id' => $this->Node->id,
			'node' => &$this->Node,
			'db_connection' => $this->DB,
			'media_collection_id' => 1,
			'unicat_db_prefix' => 'unicat_',
			);
		
		$this->Catalog = new Component_Unicat($this->__unicat_params);
	}
	
	/**
	 * Запуск модуля.
	 * 
	 * @return void
	 */
	public function run($parser_data)
	{
		
		switch ($this->mode) {
			case 'orders':
				$this->setTpl('Orders');
				
				$this->output_data['admin_mode'] = $this->admin_mode;
				if ($this->admin_mode) {
					$sql_and_user = '';
					$sql_where_user = '';
				} else {
					$sql_and_user = " AND user_id = '{$this->User->getId()}' ";
					$sql_where_user = " WHERE user_id = '{$this->User->getId()}' ";
				}

				// Постраничность
				$items_per_page = 30;
				if (isset($_GET['page']) and is_numeric($_GET['page'])) {
					$current_page = $_GET['page'];
				} else {
					$current_page = 1;
				}
				
				if ($items_per_page == 0) {
					$limit = '';
				} else {
					$start_item = ($current_page - 1) * $items_per_page;
					$limit = " LIMIT $start_item, {$items_per_page} ";
				}
				
				// Просмотр конкретного заказа
				if (isset($_GET['id']) and is_numeric($_GET['id'])) {
					$id = $_GET['id'];
					$sql = "SELECT * 
						FROM {$this->DB->prefix()}basket_orders
						WHERE order_id = '$id'
						$sql_and_user
						";
					$result = $this->DB->query($sql);
					if ($result->rowCount() == 1) {
						$row = $result->fetchObject();
						$orders[$row->order_id] = array(
							'user_id' => $row->user_id,
							'datetime' => $row->datetime,
							'amount' => $row->amount,
							'client_data' => $row->client_data,
							'data' => $row->data,
							);
					} else {
						cf_redirect('/orders/');
					}
				}
				
				$orders = array();
				$sql = "SELECT *
					FROM {$this->DB->prefix()}basket_orders
					$sql_where_user
					ORDER BY datetime DESC
					$limit
					";
				$result = $this->DB->query($sql);
				if ($result->rowCount() == 0) {
					cf_redirect('/orders/');
				}
				while ($row = $result->fetchObject()) {
					$orders[$row->order_id] = array(
						'user_id' => $row->user_id,
						'datetime' => $row->datetime,
						'amount' => $row->amount,
						'client_data' => $row->client_data,
						'data' => $row->data,
						);
				}
				
				// Подсчет общего кол-ва записей
				$sql = "SELECT count(order_id) AS cnt
					FROM {$this->DB->prefix()}basket_orders
					$sql_where_user
					";
				$result = $this->DB->query($sql);
				$row = $result->fetchObject();
				
				$this->output_data['pages'] = new Helper_Paginator(array(
						'items_count' => $row->cnt,
						'items_per_page' => $items_per_page,
						'current_page' => $current_page,
//						'all' => $all,
						'link_tpl' => '?page={PAGE}',
						)
					);
				
				$this->output_data['orders_list'] = $orders;
				break;
			case 'normal':
				if ($this->User->getId() == 0) {
					cf_redirect('/user/registration/');
				}
				
				// Удаление товаров из корзины.
				if (isset($_GET['del_item'])) {
					if (is_numeric($_GET['del_item'])) {
						$sql = " DELETE FROM {$this->DB->prefix()}basket
							WHERE item_id = '{$_GET['del_item']}'
							AND user_id = '{$this->User->getId()}'
							";
						$this->DB->exec($sql);
					}
					cf_redirect('/basket/');
				}
				
				// Действие добавления товара в корзинку.
				if (isset($_GET['add_item'])) {
					$sql = "SELECT cnt 
						FROM {$this->DB->prefix()}basket
						WHERE item_id = '{$_GET['add_item']}'
						AND user_id = '{$this->User->getId()}'
						";
					$result = $this->DB->query($sql);
					if ($result->rowCount() == 0) {
						$link = str_replace( 'http://' . $_SERVER['HTTP_HOST'] , '', $_SERVER['HTTP_REFERER'] );
						
						$sql = "
							INSERT INTO {$this->DB->prefix()}basket
								(user_id, item_id, cnt, link)
							VALUES
								('{$this->User->getId()}', '{$_GET['add_item']}', '1', '{$link}')
							";
						$this->DB->query($sql);
					}
					cf_redirect($_SERVER['HTTP_REFERER']);
				}
				
				// Действие история заказов
				if (isset($_GET['history'])) {
					
				}
				
				// Действие оформления заказа
				if (isset($_GET['action']) and $_GET['action'] == 'order') {
					$this->setTpl('Order');
					$sql = "SELECT * 
						FROM {$this->DB->prefix()}basket_customers
						WHERE user_id = '{$this->User->getId()}'
						";
					$result = $this->DB->query($sql);
					if ($result->rowCount() == 0) {
						$sql = "
							INSERT INTO {$this->DB->prefix()}basket_customers
								(user_id)
							VALUES
								('{$this->User->getId()}')
							";
						$this->DB->query($sql);
					}
					
					$sql = "SELECT * 
						FROM {$this->DB->prefix()}basket_customers
						WHERE user_id = '{$this->User->getId()}'
						";
					$result = $this->DB->query($sql);
					$row = $result->fetchObject();
					
					$form_data = array(
						'class' => 'customer_form',
						'hiddens' => array( 
							'node_id' => $this->Node->id,
							),
						'elements' => array(
							'pd[html-title]' => array(
								'type' => 'html',
								'value' => 'Для оформления заказа, необходимо заполнить следующие данные:',
								),
							'pd[name]' => array(
								'label' => 'Наименование компании',
								'type' => 'text',
								'value' => $row->name,
								),
							'pd[phone]' => array(
								'label' => 'Контактный телефон',
								'type' => 'text',
								'value' => $row->phone,
								),
							'pd[address]' => array(
								'label' => 'Адрес',
								'type' => 'text',
								'value' => $row->address,
								),
							'pd[props]' => array(
								'label' => 'Реквизиты',
								'type' => 'textarea',
								'style' => 'width: 100%;',
								'rows' => 4,
								'value' => $row->props,
								),
							),
						'buttons' => array(
							'submit[submit_order]' => array(
								'value' => 'Подтвердить заказ',
								'type' => 'submit',
								),
							),
						'help' => 'Cправка',
						);
					$this->output_data['customer_form'] = $form_data;
				}
				
				// Сообщение об успешном выполнении заказа.
				if (isset($_GET['action']) and $_GET['action'] == 'success') {
					$this->setTpl('Success');
					$sql = "SELECT * 
						FROM {$this->DB->prefix()}basket_orders
						WHERE user_id = '{$this->User->getId()}'
						ORDER BY order_id DESC
						LIMIT 1
						";
					$result = $this->DB->query($sql);
					$row = $result->fetchObject();
					$this->output_data['result'] = $row->data;
				}

				$this->output_data['basket'] = $this->getBasketData();
				
				break;
			case 'light':
				$this->setTpl('Light');
				$basket = $this->getBasketData();

				$order_price = 0;
				
				foreach ($basket as $value) {
					$order_price += $value['price'];
				}
				
				$this->output_data['order_price'] = $order_price;
				$this->output_data['cnt'] = count($basket);
				break;
			default;
		}
				
	}	

	/**
	 * NewFunction
	 *
	 * @param
	 * @return
	 */
	private function getBasketData()
	{
		$basket = array();
		
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}basket
			WHERE user_id = '{$this->User->getId()}'
			";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$item = $this->Catalog->getItem($row->item_id);
			$basket[$row->item_id] = array(
				'articul' =>  $item['content']['title']['value'],
				'img' =>  $item['content']['foto_main']['value'],
				'packing' =>  $item['content']['packing']['value'],
				'price' =>  $item['content']['price']['value'],
				'cnt' => $row->cnt,
				'link' => $row->link,
				);
		}

		return $basket;
	}
	
	/**
	 * Парсер части УРИ.
	 * 
	 * @param string $path - часть URI запроса
	 * @return array|false
	 */
/*
	public function parser($path)
	{
		$data = array();
		return $data;
	}
*/

	/**
	 * Обработчик POST данных
	 * 
	 * @param int $pd
	 * @param string $submit
	 * @return void
	 */
	public function postProcessor($pd, $submit)
	{
		switch ($submit) {
			case 'submit_order':
				$basket = $this->getBasketData();
				
				$sql = "
					UPDATE {$this->DB->prefix()}basket_customers SET
						name = {$this->DB->quote($pd['name'])},
						phone = {$this->DB->quote($pd['phone'])},
						address = {$this->DB->quote($pd['address'])},
						props = {$this->DB->quote($pd['props'])}
					WHERE user_id = '{$this->User->getId()}'
					";
				$this->DB->query($sql);
				
				$sql = "
					INSERT INTO {$this->DB->prefix()}basket_orders
						(user_id, datetime, data)
					VALUES
						('{$this->User->getId()}', NOW(), '')
					";
				$this->DB->query($sql);
				$order_id = $this->DB->lastInsertId();
				$email = $this->User->getEmail();
				
				
$client_data = "
Компания:  $pd[name]
Телефон:   $pd[phone]
Email:     $email
Адрес:     $pd[address]
Реквизиты: $pd[props]";

$data = "$client_data
------------------------

";

//Заказ №: $order_id


$order_price = 0;
$cnt = 1;
foreach ($basket as $key => $value) {
	$order_price += $value['cnt'] * $value['price'];
	$data .= $cnt++ . ") $value[articul], $value[cnt] пар. по $value[price] руб. ( http://" . $_SERVER['HTTP_HOST'] . "$value[link] )\n";
}

$data .= "\n\nНа сумму $order_price рублей.";
				
				$sql = "
					UPDATE {$this->DB->prefix()}basket_orders SET
						client_data = {$this->DB->quote($client_data)},
						data = {$this->DB->quote($data)},
						amount = '$order_price'
					WHERE order_id = $order_id
					";
				$this->DB->query($sql);
				
				// Письмо админу.
				$mail = new Zend_Mail('UTF-8');
				$mail->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);
				$mail->setBodyText($data);
				$mail->setFrom('no-reply@' . HTTP_HOST, 'Заказ №' . $order_id);
				$mail->addTo($this->email, 'admin');
				$mail->setSubject('Заказ с сайта ' . HTTP_HOST . ' от ' . date('Y-m-d H:i:s'));
//				$mail->send();

				// Письмо клиенту
				$mail2 = new Zend_Mail('UTF-8');
				$mail2->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);
				$mail2->setBodyText($data);
				$mail2->setFrom('no-reply@'. HTTP_HOST, 'Заказ c сайта ' . HTTP_HOST);
				$mail2->addTo($email, $this->User->getName());
				$mail2->setSubject('Ваш заказ с сайта ' . HTTP_HOST . ' от ' . date('Y-m-d H:i:s') . ' принят.');
//				$mail2->send();

				// Удаление корзины
				$sql = " DELETE FROM {$this->DB->prefix()}basket
					WHERE user_id = '{$this->User->getId()}'
					";
				$this->DB->exec($sql);
				
				cf_redirect('?action=success');
				
				break;
			case 'recalculate':
				foreach ($pd as $key => $value) {
					if (!is_numeric($value) or !is_int((int) $value)) {
						continue;
					}
					
					if ($value == 0) {
						$sql = " DELETE FROM {$this->DB->prefix()}basket
							WHERE item_id = '$key'
							AND user_id = '{$this->User->getId()}'
							";
						$this->DB->exec($sql);
						continue;
					}
					
					$sql = "
						UPDATE {$this->DB->prefix()}basket SET
							cnt = '$value'
						WHERE item_id = '$key'
						AND user_id = '{$this->User->getId()}'
						";
					$this->DB->query($sql);
				}
				break;
			default:
		}
	} 
	
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		$node_params = array(
			'mode' => array(
				'label' => 'Режим работы:',
				'type' => 'select',
				'value' => $this->mode,
				'options' => array(
					'light' => 'light',
					'normal' => 'normal',
					'orders' => 'orders',
					),
				),
			'admin_mode' => array(
				'label' => 'Режим админа:',
				'type' => 'checkbox',
				'value' => $this->admin_mode,
				),
			'email' => array(
				'label' => 'Емаил админа:',
				'type' => 'text',
				'value' => $this->email,
				),
			);
		return $node_params;
	}

	/**
	 * Вызывается при создании ноды.
	 * 
	 * @return array params
	 * 
	 * @todo мультиязычность.
	 */
	public function createNode()
	{
		$params = array(
			'mode' => 'normal',
			'email' => '',
			);
		return $params;
	}
	
}
