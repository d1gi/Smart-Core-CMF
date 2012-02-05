<?php
/**
 * Модуль: подписка на рассылку.
 * 
 * @package Module
 * 
 * @uses Component_Unicat
 * @uses DB
 * @uses Permissions
 * 
 * @version 2012-01-31.0
 */
class Module_Subscribe extends Module
{
	/**
	 * Кол-во записей на страницу.
	 * @var int
	 */
	protected $items_per_page;

	/**
	 * email с которого будут рассылаться письма активации.
	 * @var string
	 */
	protected $activate_from_email;
	
	/**
	 * Тема письма активации.
	 * @var string
	 */
	protected $activate_from_email_subject;
	
	/**
	 * Префикс CSS классов.
	 * @var string
	 */
	protected $css_prefix;
	
	/**
	 * Объект компонента Unicat
	 * @var object
	 */
	protected $Unicat;
	protected $unicat_params;

	/**
	 * Обязательные свойства записей.
	 * @var array|null
	 */
	protected $unicat_requred_items_properties = null;

	/**
	 * Обязательные стуктуры.
	 * @var array|null
	 */
	protected $unicat_requred_structures = null;
	
	/**
	 * Подключение юниката к БД.
	 * @var int
	 */
	protected $unicat_database_id;
	
	/**
	 * Конструктор.
	 * 
	 * @return void
	 */
	protected function init()
	{
		$this->Node->setDefaultParams(array(
			'css_prefix'					=> 'subscribe_releases_',
			'entity_id'						=> 0,
			'media_collection_id'			=> 0,
			'items_per_page' 				=> 15,
			'unicat_db_prefix'				=> 'unicat_',
			'unicat_database_id'			=> 0,
			'activate_from_email'			=> 'subscribe-activation@' . HTTP_HOST,
			'activate_from_email_subject'	=> 'Подписка на рыссылки с сайта ' . HTTP_HOST,
			));
		
		$this->activate_from_email			= $this->Node->getParam('activate_from_email');
		$this->activate_from_email_subject	= $this->Node->getParam('activate_from_email_subject');
		$this->css_prefix	 				= $this->Node->getParam('css_prefix');
		$this->items_per_page 				= $this->Node->getParam('items_per_page');
		$this->unicat_database_id			= $this->Node->getParam('unicat_database_id');
		
		// При database_id = 0 модуль будет использовать тоже подключение, что и ядро, иначе создаётся новое подключение.
		if ($this->unicat_database_id != 0) {
			// @todo для совместимости с эмуляцией функции get_called_class для РНР 5.2, дальше для PHP 5.3 only можно будет записывать в одну строку, без $con_data.
			$db_key = 'DB.' . $this->unicat_database_id;
			if (!Registry::has($db_key)) {
				$con_data = $this->DB_Resources->getConnectionData($this->unicat_database_id);
				Registry::set($db_key, DB::connect($con_data));
			}
			$UnicatDB = Registry::get($db_key);
			unset($con_data, $db_key);
		} else {
			$UnicatDB = &$this->DB;
		}
		
		$this->unicat_params = array(
			'path_prefix'			=> 'releases/',
			'entity_id'				=> $this->Node->getParam('entity_id'),
			'node'					=> &$this->Node,
			'db_connection'			=> $UnicatDB,
			'media_collection_id'	=> $this->Node->getParam('media_collection_id'),
			'unicat_db_prefix'		=> $this->Node->getParam('unicat_db_prefix'),
			'requred_structures'	=> $this->unicat_requred_structures,
			'requred_items_properties'	=> $this->unicat_requred_items_properties,
			);
		$this->Unicat = new Component_Unicat($this->unicat_params);		
	}
	
	/**
	 * Запуск модуля.
	 * 
	 * @param array $params
	 * @return void
	 */
	public function run($params)
	{
		if ($this->unicat_params['entity_id'] == 0) {
			if ($this->Permissions->isRoot()) {
				$this->View->create_entity_form_data = $this->Unicat->getCreateEntityFormData();
				$this->View->setTpl($this->Unicat->getCreateEntityFormTemplate());
			}
			return;
		}
		
		if (($this->Permissions->isRoot() or $this->Permissions->isAdmin()) and @$params['action'] !== 'releases') {
			$this->View->manage_link = $this->Node->getUri() . 'releases/';
		}
		
		switch ($params['action']) {
			case 'activate':
				if ($this->activate($params['code'])) {
					$this->View->notice_message = 'Активация на подписку прошла успешно.';
				} else {
					$this->View->error_message = $this->getErrorMessage();
				}
				return;
				break;
			case 'email':
				if ($this->getSubscriberId($params['email'])) {
					$this->View->notice_message = 'Указанный email уже существует в базе рассылок. Вы можете ввести другой email и продолжить процедуру подписки либо отписаться от рассылки.';
					$this->View->subscribe_form = $this->getUnSubscribeFormData($params['email']);
				} else {
					$this->createActivation($params['email']);
					$this->View->success_message = 'На указанный email: ' . $params['email'] . ' отправлено письмо с кодом активации подписки на рассылку.';
				}
				return;
				break;
			case 'delete':
				if ($this->getSubscriberId($params['code'])) {
					$this->createDeleteActivation($params['code']);
					$this->View->success_message = 'На указанный email: ' . $params['code'] . ' отправлено письмо с кодом подтверждения операции.';
				} else {
					if ($this->delete($params['code'])) {
						$this->View->notice_message = 'Удаление подписки прошло успешно.';
					} else {
						$this->View->error_message = $this->getErrorMessage();
					}
				} 
				return;
				break;
			case 'releases':
				$this->Breadcrumbs->add('releases/', 'Управление');
				$this->View->css_prefix = $this->css_prefix;
				$options = array(
					'order' => array(
						'i.item_id' => 'DESC',
						),
					);
				$this->View->items = $this->Unicat->getItems($options);
				return;
				break;
			default;
		}
		
		$this->View->error_messages = $this->Session_Force->error_messages;
		$this->View->subscribe_form = $this->getSubscribeFormData($this->Session_Force->email);
	}	
	
	/**
	 * NewFunction
	 *
	 * @param
	 * @return
	 */
	public function delete($code)
	{
		$sql = "SELECT subscriber_id
			FROM {$this->DB->prefix()}subscribers_submit
			WHERE site_id = '{$this->Env->site_id}'
			AND action = 'delete'
			AND code = {$this->DB->quote($code)} ";
		$row = $this->DB->getRow($sql);
		if (empty($row)) {
			$this->setErrorCode(1);
			$this->setErrorMessage('Код не действителен.');
			return false;
		} else {
			$sql = "DELETE FROM {$this->DB->prefix()}subscribers_submit
				WHERE site_id = '{$this->Env->site_id}'
				AND subscriber_id  = '{$row['subscriber_id']}' 
				AND action = 'delete' ";
			$this->DB->exec($sql);
			
			$sql = "DELETE FROM {$this->DB->prefix()}subscribers
				WHERE site_id = '{$this->Env->site_id}'
				AND subscriber_id  = '{$row['subscriber_id']}' ";
			$this->DB->exec($sql);

			$sql = "DELETE FROM {$this->DB->prefix()}subscribers_rubrics_relation
				WHERE site_id = '{$this->Env->site_id}'
				AND subscriber_id  = '{$row['subscriber_id']}' ";
			$this->DB->exec($sql);
			return true;
		}
	}
	
	/**
	 * Активация подписки
	 *
	 * @param string $code
	 * @return bool
	 */
	public function activate($code)
	{
		// @todo , rubrics_list
		$sql = "SELECT subscriber_id
			FROM {$this->DB->prefix()}subscribers_submit
			WHERE site_id = '{$this->Env->site_id}'
			AND action = 'subscribe'
			AND code = {$this->DB->quote($code)} ";
		$row = $this->DB->getRow($sql);
		if (empty($row)) {
			$this->setErrorCode(1);
			$this->setErrorMessage('Код активации не действителен.');
			return false;
		} else {
			$sql = "
				UPDATE {$this->DB->prefix()}subscribers SET
					is_active = '1',
					activate_datetime = NOW()
				WHERE site_id = '{$this->Env->site_id}'
				AND subscriber_id  = '{$row['subscriber_id']}' ";
			$this->DB->query($sql);
			
			$sql = "DELETE FROM {$this->DB->prefix()}subscribers_submit
				WHERE site_id = '{$this->Env->site_id}'
				AND subscriber_id  = '{$row['subscriber_id']}' 
				AND action = 'subscribe' ";
			$this->DB->exec($sql);
			
			$sql = "
				INSERT INTO {$this->DB->prefix()}subscribers_rubrics_relation
					(subscriber_id, site_id, rubric_id)
				VALUES
					('{$row['subscriber_id']}', '{$this->Env->site_id}', '0') ";
			$this->DB->query($sql);
			return true;
		}
	}
	
	/**
	 * Подтвердить отписку от подписки :)
	 *
	 * @param string $email
	 * @return bool
	 */
	public function createDeleteActivation($email)
	{
		$subscriber_id = $this->getSubscriberId($email);
		$code = md5(microtime() . $email . $subscriber_id);
		$rubrics_list = serialize(array());
		
		$sql = "SELECT count(subscriber_id) AS cnt
			FROM {$this->DB->prefix()}subscribers_submit
			WHERE site_id = '{$this->Env->site_id}'
			AND subscriber_id = '$subscriber_id' 
			AND action = 'delete' ";
		$row = $this->DB->getRow($sql);
		if ($row['cnt'] == 0) {
			$sql = "
				INSERT INTO {$this->DB->prefix()}subscribers_submit
					(site_id, subscriber_id, datetime, action, rubrics_list, code )
				VALUES
					('{$this->Env->site_id}', '$subscriber_id', NOW(), 'delete', {$this->DB->quote($rubrics_list)}, {$this->DB->quote($code)} ) ";
			$this->DB->exec($sql);
			
			$content = "Для отказа от подписки перейдите по ссылке: http://" . HTTP_HOST . $this->Node->getUri() . "delete/$code";
			$this->sendSubmitEmail($email, $content);
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Создание активации.
	 *
	 * @param string $email
	 * @param array $rubrics
	 * @return bool @todo 
	 */
	public function createActivation($email, $rubrics = array())
	{
		$sql = "
			INSERT INTO {$this->DB->prefix()}subscribers
				(site_id, create_datetime, email )
			VALUES
				('{$this->Env->site_id}', NOW(), {$this->DB->quote($email)} ) ";
		$this->DB->query($sql);
		$subscriber_id = $this->DB->lastInsertId();
		
		/*
		$rubrics = array();
		foreach ($pd['rubrics'] as $rubric_id => $is_enable) {
			if ($is_enable == 1) {
				$rubrics[] = $rubric_id;
			}
		}
		*/
		$rubrics_list = $this->DB->quote(serialize($rubrics));
		
		$code = md5($rubrics_list . microtime() . $email . $subscriber_id );
		
		$sql = "
			INSERT INTO {$this->DB->prefix()}subscribers_submit
				(site_id, subscriber_id, datetime, action, rubrics_list, code )
			VALUES
				('{$this->Env->site_id}', '$subscriber_id', NOW(), 'subscribe', $rubrics_list, {$this->DB->quote($code)} ) ";
		$this->DB->exec($sql);
		
		$content = "Для активации подписки перейдите по ссылке: http://" . HTTP_HOST . $this->Node->getUri() . "activate/$code";
		$this->sendSubmitEmail($email, $content);
	}
	
	/**
	 * Отправка подтверждающего емаила.
	 *
	 * @param string $email
	 * @param string $content
	 * @return void
	 */
	protected function sendSubmitEmail($email, $content)
	{
		$Maillist = new Maillist();
		$Maillist->add(
			$this->activate_from_email,
			$this->activate_from_email_subject,
			$content,
			array($email)
			);
	}
	
	/**
	 * Получить ID подписчика по его емайлу.
	 *
	 * @param string $email
	 * @return int|null
	 */
	public function getSubscriberId($email)
	{
		$sql = "SELECT subscriber_id FROM {$this->DB->prefix()}subscribers WHERE site_id = '{$this->Env->site_id}' AND email = {$this->DB->quote(trim($email))} ";
		$row = $this->DB->getRow($sql);
		return empty($row) ? null : $row['subscriber_id'];
	}
	
	/**
	 * Парсер части УРИ.
	 * 
	 * @param string $path - часть URI запроса
	 * @return array|false
	 */
	public function router($path)
	{
		$data = array();
		$uri_parts = Uri::parse($path);
		
		// @todo проверку на наличие хешей в запросе.
		switch ($uri_parts[0]['name']) {
			case 'activate':
				if (isset($uri_parts[1]['name'])) {
					$data = array(
						'params' => array(
							'action' => 'activate',
							'code' => $uri_parts[1]['name'],
						),
					);
				} else {
					return null;
				}
				break;
			case 'delete':
				if (isset($uri_parts[1]['name'])) {
					$data = array(
						'params' => array(
							'action' => 'delete',
							'code' => $uri_parts[1]['name'],
						),
					);
				} else {
					return null;
				}
				break;
			case 'update':
					$data = array(
						'params' => array(
							'action' => 'update',
							'code' => $uri_parts[1]['name'],
						),
					);
				break;
			case 'releases':
				if ($this->Permissions->isRoot() or $this->Permissions->isAdmin()) {
					$data = array(
						'params' => array(
							'action' => 'releases',
							'uri' => str_replace('releases/', '', $path),
						),
					);
				} else {
					return null;
				}
				break;
			default;
					$data = array(
						'params' => array(
							'action' => 'email',
							'email' => $uri_parts[0]['name'],
						),
					);
		}
		$data['action'] = 'run';

		return $data;
	}
	
	/**
	 * Получить форму отписки ;).
	 *
	 * @param string $email
	 * @return array
	 */
	public function getUnSubscribeFormData($email)
	{
		return array(
			'action' => Folder::getUri($this->Node->folder_id),
			'hiddens' => array( 
				'node_id' => $this->Node->id,
				),
			'elements' => array(
				'pd[email]' => array(
					'label' => 'Ваш email',
					'type' => 'string',
					'value' => $email,
					'required' => true,
					),
				),
			'autofocus' => 'pd[email]',
			'buttons' => array(
				'submit[unsubscribe]' => array(
					'value' => 'Отписаться',
					'type' => 'submit',
					),
				'submit[subscribe]' => array(
					'value' => 'Подписаться',
					'type' => 'submit',
					),
				),
			'help' => 'Cправка'
			);
	}

	/**
	 * Получить основную форму подписки.
	 *
	 * @param string $email
	 * @return array
	 */
	public function getSubscribeFormData($email)
	{
		return array(
			'action' => Folder::getUri($this->Node->folder_id),
			'hiddens' => array( 
				'node_id' => $this->Node->id,
				),
			'elements' => array(
				'pd[email]' => array(
					'label' => 'Ваш email',
					'type' => 'string',
					'value' => $email,
					'required' => true,
					),
				),
			'autofocus' => 'pd[email]',
			'buttons' => array(
				'submit[subscribe]' => array(
					'value' => 'Подписаться',
					'type' => 'submit',
					),
				),
			'help' => 'Cправка'
			);
	}

	/**
	 * Форма быстрой подписки.
	 *
	 * @param
	 * @return array
	 */
	public function getQuickFormData()
	{
		$disabled = ($this->Node->folder_id == $this->Env->current_folder_id or $this->Unicat->isTablesExist() == false) ? true : false;
		
		return array(
			'action' => Folder::getUri($this->Node->folder_id),
			'class' => 'quick_form',
			'hiddens' => array( 
				'node_id' => $this->Node->id,
				),
			'elements' => array(
				'pd[email]' => array(
					//'label' => 'Email',
					'type' => 'string',
					'disabled' => $disabled,
					'value' => 'Введите ваш Email',
					),
				),
			'buttons' => array(
				'submit[quick_subscribe]' => array(
					'value' => 'Подписаться',
					'type' => 'submit',
					'disabled' => $disabled,
					),
				),
			'help' => 'Cправка'
			);
	}
	
	/**
	 * Обработчик хуков.
	 *
	 * @param string $method - имя вызываемого метода.
	 * @param array $args - массив с аргументами.
	 */
	public function hook($method, $args = null)
	{
		if ($this->unicat_params['entity_id'] == 0) {
			return null;
		}
		
		$data = array();
		switch ($method) {
			case 'getQuickForm':
				$data['quick_form'] = $this->getQuickFormData();
				break;
			default;
		}
		
		return $data;
	}
	
	/**
	 * Обработчик POST данных
	 * 
	 * @param int $pd
	 * @param string $submit
	 * @return void
	 */
	public function postProcessor($pd, $submit)
	{
		//cmf_dump($this->Session); cmf_dump($_POST); exit;		
		$this->Unicat->postProcessor($pd, $submit);
		
		// Валидация емаила
		$Validator = new Helper_Validator();
		
		$email = trim(@$pd['email']);
		
		switch ($submit) {
			case 'quick_subscribe':
				if ($Validator->email($email)) {
					cmf_redirect($this->Node->getUri() . $email);
				} else {
					if (!empty($email)) {
						$this->Session_Force->error_messages = $Validator->getMessages();
						$this->Session_Force->email = $email;
					}
					cmf_redirect($this->Node->getUri());
				}
				break;
			case 'subscribe': // @todo 
				if ($Validator->email($email)) {
					cmf_redirect($this->Node->getUri() . $email);
				} else {
					if (!empty($email)) {
						$this->Session_Force->error_messages = $Validator->getMessages();
						$this->Session_Force->email = $email;
					}
					cmf_redirect($this->Node->getUri());
				}
				break;
			case 'unsubscribe':
				if ($Validator->email($email)) {
					cmf_redirect($this->Node->getUri() . 'delete/' . $email);
				} else {
					if (!empty($email)) {
						$this->Session_Force->error_messages = $Validator->getMessages();
						$this->Session_Force->email = $email;
					}
					cmf_redirect($this->Node->getUri());
				}
				break;
			default:
		}
	}
}