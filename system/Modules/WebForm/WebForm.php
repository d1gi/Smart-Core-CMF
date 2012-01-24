<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Module Веб-формы.
 * 
 * @uses Session_Force
 * 
 * @package Module
 * @version 2012-01-10.0
 */
class Module_WebForm extends Module
{
	/**
	 * Form ID.
	 * @var int
	 */
	protected $form_id;
	
	/**
	 * CSS класс тега <form>
	 * @var string
	 */
	protected $css_class;
	
	/**
	 * Период для отсылки писем с напоминаниями.
	 * @var int
	 */
	protected $reminder_period;
	
	/**
	 * Отправлять результаты по email.
	 */
	protected $email_from;
	protected $email_to;
	
	/**
	 * Оповещать на емаил сразу после заполения формы.
	 * @var bool
	 */
	protected $is_email_immediately;
	
	/**
	 * Конструктор.
	 * 
	 * @return void
	 */
	protected function init()
	{
		$this->Node->setDefaultParams(array(
			'form_id'		=> 0,
			'css_class'		=> 'default-form',
			'email_from'	=> 'Новые сообщения через форму <no-reply@' . HTTP_HOST . '>',
			'email_to'		=> 'webmaster@' . HTTP_HOST,
			'reminder_period'	=> 0,
			'is_email_immediately'	=> 1,
			));

		$this->form_id		= $this->Node->getParam('form_id');
		$this->css_class	= $this->Node->getParam('css_class');
		$this->email_from	= $this->Node->getParam('email_from');
		$this->email_to		= $this->Node->getParam('email_to');
		$this->reminder_period	= $this->Node->getParam('reminder_period');
		$this->is_email_immediately	= $this->Node->getParam('is_email_immediately');
	}
	
	/**
	 * Запуск модуля.
	 * 
	 * @return void
	 */
	public function run($parser_data)
	{
		if ($this->form_id == 0) {
			return;
		}
		
		// Сообщение об успешной отправке формы.
		$this->View->send_success = $this->Session_Force->send_success;
		if (!empty($this->View->send_success)) {
			$this->View->setTpl('Success');
			return true;
		}
		
		$webform_data		 = $this->getWebformData($this->form_id);
		$webform_data_fields = $this->getWebformFields($this->form_id);
		
		$form_data = array(
			'class'	  => $this->css_class,
			'hiddens' => array(
				'node_id' => $this->Node->id,
				),
			'buttons' => array(
				'submit[send]' => array(
					'type' => 'submit',
					'value' => $webform_data['submit_title'],
					),
				),
			);
		// Восстановление данных, введенных пользователем.
		$pd = $this->Session_Force->form_data;
		foreach ($webform_data_fields as $key => $value) {
			$default_value = isset($pd[$key]) ? $pd[$key] : $value['default_value'];
			$element = array(
				'label' => $value['title'],
				'required' => $value['is_required'],
				'type' => $value['type'],
				'value' => $default_value,
				);
				
			try {
				foreach (Zend_Config_Yaml::decode($value['attrs']) as $attr_name => $attr_value) {
					$element[$attr_name] = $attr_value;
				}
			} catch (Exception $e){
				
			}
				
			$data = $this->Node->event('getFormFieldData', array(
				'property_id' => $key, 
				'value' => $default_value,
				'name' => 'pd[content][' . $key . ']',
				));
			
			if (empty($data)) {
				switch ($value['type']) {
					case 'select':
						$options = array();
						if ($value['is_required'] == 0) {
							$options[''] = '';
						}

						try {
							foreach (Zend_Config_Yaml::decode($value['params']) as $param_name => $param_value) {
								$options_list[$param_name] = $param_value;
							}
						} catch (Exception $e){
							foreach (explode(';', $value['params']) as $value) {
								$options_list[$value] = $value;
							}
						}
						
						foreach ($options_list as $option_key => $option_value) {
							$options[trim($option_key)] = trim($option_value);
						}
						$element['options'] = $options;
						break;
					case 'text':
					case 'textarea':
						$element['type'] = 'textarea';
						break;
					default;
				}
			} else {
				$element['type']	= $data['type'];
				if (isset($data['value'])) {
					$element['value']	= $data['value'];
				}
				if (isset($data['options'])) {
					$element['options']	= $data['options'];
				}
			}
			
			$form_data['elements']['pd[' . $key . ']'] = $element;
		} // end foreach ($webform_data_fields as $key => $value)
		
		// CAPTCHA
		if ($webform_data['use_captcha']) {
			$Node = new Node($webform_data['use_captcha']);
			$form_data['elements']['pd[captcha_img]'] = array(
				'label' => '',
				'type' => 'html',
				'value' => $Node->hook('getHtmlCode'),
				);
			$form_data['elements']['pd[captcha_code]'] = array(
				'label' => 'Код с картинки',
				'type' => 'text',
				);
		}
		
		$this->View->messages = $this->Session_Force->messages;
		$this->View->webform = $form_data;
	}	
	
	/**
	 * Выполнение задач по расписанию.
	 *
	 * @param
	 * @return
	 */
	public function cron()
	{
		
	}
	
	/**
	 * Получить список всех форм.
	 *
	 * @return array
	 */
	protected function getWebformsList()
	{
		$data = array();
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}webforms AS wf, 
				 {$this->DB->prefix()}webforms_translation AS wft
			WHERE wf.site_id = '{$this->Env->site_id}'
			AND wf.site_id = wft.site_id
			AND wf.form_id = wft.form_id ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$sql2 = "SELECT count(form_id) AS cnt
				FROM {$this->DB->prefix()}webforms_fields 
				WHERE site_id = '{$this->Env->site_id}'
				AND form_id = {$row->form_id} ";
			$result2 = $this->DB->query($sql2);
			$row2 = $result2->fetchObject();
			$fields_count = $row2->cnt;
			
			$sql2 = "SELECT count(result_id) AS cnt
				FROM {$this->DB->prefix()}webforms_results
				WHERE site_id = '{$this->Env->site_id}'
				AND form_id = {$row->form_id}
				";
			$result2 = $this->DB->query($sql2);
			$row2 = $result2->fetchObject();
			$results_count = $row2->cnt;
			
			$data[$row->form_id] = array(
				'form_id'		=> $row->form_id,
				'name'			=> $row->name,
				'use_captcha'	=> $row->use_captcha,
				'params'		=> $row->params,
				'title'			=> $row->title,
				'descr'			=> $row->descr,
				'submit_title'	=> $row->submit_title,
				'fields_count'	=> $fields_count,
				'results_count'	=> $results_count,
				'owner_id'		=> $row->owner_id,
				'create_datetime' => $row->create_datetime,
				'success_message' => $row->success_message,
				);
		}
		return $data;
	}
	
	/**
	 * Получить список полей веб-формы.
	 *
	 * @param int $form_id
	 * @return array
	 */
	protected function getWebformFields($form_id, $is_active = false)
	{
		if ($is_active === false) {
			$is_active = "AND is_active = '1' ";
		} else {
			$is_active = '';
		}
		
		$data = array();
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}webforms_fields
			WHERE site_id = '{$this->Env->site_id}'
			AND form_id = '$form_id'
			$is_active
			ORDER BY pos ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$data[$row->field_id] = array(
				'pos' => $row->pos,
				'name' => $row->name,
				'type' => $row->type,
				'default_value' => $row->default_value,
				'attrs' => $row->attrs,
				'params' => $row->params,
				'validators' => $row->validators,
				'is_active' => $row->is_active,
				'is_required' => $row->is_required,
				'service_comment' => $row->service_comment,
				'create_datetime' => $row->create_datetime,
				'owner_id' => $row->owner_id,
				'title' => $row->title,
				'descr' => $row->descr,
				);
		}
		return $data;
	}
		
	/**
	 * Получить данные поля.
	 *
	 * @param int $form_id
	 * @param int $field_id
	 * @return array
	 */
	protected function getFieldData($form_id, $field_id, $is_active = false)
	{
		$data = $this->getWebformFields($form_id, $is_active);
		return $data[$field_id];
	}
	
	/**
	 * Получить данные формы.
	 *
	 * @param int $form_id
	 * @return array
	 */
	protected function getWebformData($form_id)
	{
		$data = $this->getWebformsList();
		return $data[$form_id];
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
		switch ($submit) {
			case 'send':
				$this->sendResult($pd);
				break;
			default:
		}
	} 
	
	/**
	 * Отправить результат заполнения формы.
	 *
	 * @param array $pd
	 * @return bool
	 */
	protected function sendResult($pd)
	{
		$webform_data = $this->getWebformData($this->form_id);
		$webform_data_fields = $this->getWebformFields($this->form_id);

		$success = true;
		$error_messages = array();
		$result_data = array();
		foreach ($webform_data_fields as $key => $value) {
			// Проверка на заполненность поля, если оно является обязательным.
			if ($value['is_required'] == 1 and strlen($pd[$key]) == 0) {
				$success = false;
				$error_messages[$value['name']] = 'Необходимо заполнить поле: ' . $value['title'];
				continue;
			}
			
			try {
				$Validator = new Helper_Validator();
				foreach (Zend_Config_Yaml::decode($value['validators']) as $validator_name => $validator_value) {
					
					// Если валидатор email, но поле не указано, но валидиовать ненадо. 
					if ($validator_name === 'email' and strlen($pd[$key]) == 0) {
						continue;
					}
					
					if ($Validator->isValid($pd[$key], $validator_name, $validator_value)) {
					} else {
						$success = false;
						$tmp = '';
						foreach ($Validator->getMessages() as $msg) {
							$tmp .= "$msg<br />\n";
						}
						$error_messages[$value['name']] = $tmp;
						continue;
					}
				}
			} catch (Exception $e){
			}
			
			$result_data[$key] = array(
				'name' => $value['name'],
				'title' => $value['title'],
				'content' => $pd[$key],
				);
		}
		
		// Проверка каптчи.
		$capcha_passed = true;
		if ($webform_data['use_captcha']) {
			$Node = new Node($webform_data['use_captcha']);
			if ($pd['captcha_code'] !== $Node->hook('getKeyString')) {
				$error_messages['captcha'] = 'Неверно введен код с картинки';
				$capcha_passed = false;
			}
		}

		if (!$success or !$capcha_passed) {
			$this->Session_Force->messages = $error_messages;
			$this->Session_Force->form_data = $pd;
			return false;
		}
		
		$Browser 		 = new Browser();
		$browser 		 = $this->DB->quote($Browser->getBrowser());
		$browser_version = $this->DB->quote($Browser->getVersion());
		$platform 		 = $this->DB->quote($Browser->getPlatform());
		$user_agent		 = $this->DB->quote($_SERVER['HTTP_USER_AGENT']);
		$ip			 	 = $this->DB->quote($_SERVER['REMOTE_ADDR']);
		$result_data  	 = $this->DB->quote(serialize($result_data));

		$sql = "
			INSERT INTO {$this->DB->prefix()}webforms_results
				(site_id, form_id, datetime, language_id, sender_user_id, ip, browser, browser_version, platform, user_agent, result_data)
			VALUES
				('{$this->Env->site_id}', '{$this->form_id}', NOW(), '{$this->Env->language_id}', '{$this->Env->user_id}', $ip, $browser, $browser_version, $platform, $user_agent, $result_data) ";
		$this->DB->query($sql);

		$result_id = $this->DB->lastInsertId();
		
		$tmp_result_data = $this->getResults(array(
				'form_id' => $this->form_id,
				'result_id' => $result_id)
				);
		
		$content = '';
		foreach ($tmp_result_data[$result_id]['result_data'] as $key => $value) {
			$content .= "$value[title]: $value[content] \n\n";
		}
		
		$Maillist = new Maillist();
		$Maillist->add(
			$this->email_from,
			'Новое сообщение с сайта: ' . HTTP_HOST,
			$content,
			array($this->email_to)
			);
		
		$this->Session_Force->send_success = $webform_data['success_message'];
		return true;
	}

	/**
	 * Получить результаты форм.
	 * 
	 * Формат массива $options:
	 *  - result_id
	 *  - only_unread
	 * 
	 * @param array $options
	 * @return array
	 * 
	 * @todo постраничность
	 */
	public function getResults($options)
	{
		if (isset($options['form_id']) and is_numeric($options['form_id'])) {
			$form_id = $options['form_id'];
		} else {
			$form_id = $this->form_id;
		}
		
		$sql_result = '';
		
		if (isset($options['result_id']) and is_numeric($options['result_id'])) {
			$sql_result .= " AND result_id = '$options[result_id]' ";
		}
		
		if (isset($options['only_unread']) and $options['only_unread'] == 1) {
			$sql_result .= " AND is_readed = '0' ";
		}
		
		// AND language_id = '{$this->Env->language_id}'
		$data = array();
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}webforms_results
			WHERE site_id = '{$this->Env->site_id}'
			AND form_id = '$form_id'
			$sql_result
			ORDER by datetime DESC
			LIMIT 0, 100 ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$data[$row->result_id] = array(
				'result_id'			=> $row->result_id,
				'result_data'		=> unserialize($row->result_data),
				'datetime'			=> $row->datetime,
				'readed_datetime'	=> $row->readed_datetime,
				'is_readed'			=> $row->is_readed,
				'reader_user_id'	=> $row->reader_user_id,
				'sender_user_id'	=> $row->sender_user_id,
				'ip'				=> $row->ip,
				'browser'			=> $row->browser,
				'browser_version'	=> $row->browser_version,
				'platform'			=> $row->platform,
				'user_agent'		=> $row->user_agent,
				);
		}
		return $data;
	}
	
	/**
	 * Обработчик хуков.
	 *
	 * @param string $method - имя вызываемого метода.
	 * @param array $args - массив с аргументами.
	 */
	public function hook($method, $args = null)
	{
		$data = false;
		switch ($method) {
			case 'getNewResults':
			case 'getNewRequests':
				$data = $this->getResults(array(
					'only_unread' => true,
					));
				break;
			case 'getRequestData';
			case 'getResultData';
				$tmp = $this->getResults(array(
					'result_id' => $args['result_id'],
					));
				$data = $tmp[$args['result_id']];
				break;
			default;
		}
		return $data;
	}
	
}