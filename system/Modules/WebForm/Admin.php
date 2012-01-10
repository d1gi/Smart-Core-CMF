<?php 
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс с административными методами.
 * 
 * @version 2012-01-09.0
 */
class Module_WebForm_Admin extends Module_WebForm implements Admin_ModuleInterface
{
	/**
	 * 
	 */
	public function getFrontControls() 
	{
		$this->default_action = 'manage';
		$items = array(
			'manage' => array(
				'popup_window_title' => 'Управление веб-формами',
				'title' => 'Управление',
				'link' => ADMIN . '/module/WebForm/',
				'ico' => 'edit',
				),
			);
		return $items;
	}
	
	/**
	 * Управление через панель управления.
	 *
	 * @param string $uri_path - часть ури
	 * @return array
	 */
	public function admin($uri_path)
	{
		if (count($_POST) > 0) {
			$this->adminPostProcessor();
		}

		// Удаление формы.
		if (isset($_GET['del_form']) and is_numeric($_GET['del_form'])) {
			$this->deleteWebform($_GET['del_form']);
			cf_redirect(HTTP_ROOT . ADMIN . '/module/WebForm/');
		}
		
		$this->setTpl('Admin');
		$data = array();
		
		$result = $this->DB->query("SHOW TABLES LIKE '{$this->DB->prefix()}webforms' ");
		if ($result->rowCount() == 0) {
			return $data;
		}
		
		$uri_path_parts = explode('/', $uri_path);
		
		// Выбрано редактирование формы
		if (is_numeric($uri_path_parts[0])) {
			$data['webform_data'] = $this->getWebformData($uri_path_parts[0]);
			$this->EE->addBreadCrumb($uri_path_parts[0] . '/', $data['webform_data']['title']);
			$data['menu'] = array(
				'common' => array(
					'title' => 'Основные настройки веб-формы',
					'link' => $this->EE->breadcrumbs[count($this->EE->breadcrumbs) - 1]['uri'],
					),
				'results' => array(
					'title' => 'Результаты',
					'link' => $this->EE->breadcrumbs[count($this->EE->breadcrumbs) - 1]['uri'] . 'results/',
					),
				'fields' => array(
					'title' => 'Поля формы',
					'link' => $this->EE->breadcrumbs[count($this->EE->breadcrumbs) - 1]['uri'] . 'fields/',
					),
				);
			switch ($uri_path_parts[1]) {
				case 'results':
					$data['menu']['results']['selected'] = true;
					$this->EE->addBreadCrumb($uri_path_parts[0] . '/', 'Результаты');
					// Просмотр результата.
					if (isset($uri_path_parts[2]) and is_numeric($uri_path_parts[2])) {
						$tmp = $this->getResults(array(
							'form_id' => $uri_path_parts[0],
							'result_id' => $uri_path_parts[2],
							));
						$data['result'] = $tmp[$uri_path_parts[2]];
					}
					// Просмотр всех результатов.
					else {
						$data['results'] = $this->getResults(array(
							'form_id' => $uri_path_parts[0],
							));
					}
					break;
				case 'fields':
					$this->EE->addBreadCrumb($uri_path_parts[1] . '/', 'Поля формы');
					$data['menu']['fields']['selected'] = true;
					// Редактирование поля.
					if (isset($uri_path_parts[2]) and is_numeric($uri_path_parts[2])) {
						$this->EE->addBreadCrumb($uri_path_parts[0] . '/', 'Редактирование поля: ' . $uri_path_parts[2]);
						$data['edit_webform_field_form_data'] = $this->getEditFieldFormData($uri_path_parts[0], $uri_path_parts[2]);
					}
					// Список полей.
					else {
						$data['webform_fields'] = $this->getWebformFields($uri_path_parts[0], true);
						$data['create_webform_field_form_data'] = $this->getCreateFieldFormData($uri_path_parts[0]);
					}
					break;
				case '':
					$data['menu']['common']['selected'] = true;
					$data['edit_webform_form_data'] = $this->getEditFormData($data['webform_data']);
					break;
				default;
			}
		}
		// Список всех форм и форма создания новой.
		elseif (empty($uri_path_parts[0])) {
			$data['webforms_list'] = $this->getWebformsList();
			$data['create_webform_form_data'] = $this->getCreateFormData();
		}
		return $data;
	}
	
	/**
	 * Получить форму создания поля.
	 *
	 * @param int $form_id
	 * @return array
	 */
	public function getCreateFieldFormData($form_id)
	{
		return array(
			'hiddens' => array(
				'pd[form_id]' => $form_id,
				),
			'elements' => array(
				'pd[is_active]' => array(
					'label' => 'Включено',
					'type' => 'checkbox',
					'value' => false,
					),
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'text',
					'value' => '',
					),
				'pd[name]' => array(
					'label' => 'Техническое имя',
					'type' => 'text',
					'value' => '',
					),
				),
			'autofocus' => 'pd[title]',
			'buttons' => array(
				'submit[create_field]' => array(
					'type' => 'submit',
					'value' => 'Создать поле',
					),
				),
			);
	}
	
	/**
	 * Получить форму редактирования поля.
	 *
	 * @param int $form_id
	 * @param int $field_id
	 * @return array
	 */
	public function getEditFieldFormData($form_id, $field_id)
	{
		$data = $this->getFieldData($form_id, $field_id, true);
		return array(
			'hiddens' => array(
				'pd[form_id]' => $form_id,
				'pd[field_id]' => $field_id,
				),
			'elements' => array(
				'_create_datetime' => array(
					'label' => 'Создано',
					'type' => 'html',
					'value' => $data['create_datetime'] . ' (by user id: ' .$data['owner_id'] . ')',
					),
				'pd[is_active]' => array(
					'label' => 'Включено',
					'type' => 'checkbox',
					'value' => $data['is_active'],
					),
				'pd[is_required]' => array(
					'label' => 'Обязателен',
					'type' => 'checkbox',
					'value' => $data['is_required'],
					),
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'text',
					'value' => $data['title'],
					),
				'pd[name]' => array(
					'label' => 'Техническое имя',
					'type' => 'text',
					'value' => $data['name'],
					),
				'pd[pos]' => array(
					'label' => 'Позиция',
					'type' => 'text',
					'value' => $data['pos'],
					),
				'pd[type]' => array(
					'label' => 'Тип',
					'type' => 'select',
					'value' => $data['type'],
					'options' => array(
						'string' => 'Строка (string)',
						'text' => 'Текст (text)',
						'date' => 'Дата (date)',
						'datetime' => 'Дата и время (datetime)',
						'img' => 'Изображение (img)',
						'file' => 'Файл (file)',
						'select' => 'Выпадающий список (select)',
						'multiselect' => 'Список с множественным выбором (multiselect)',
						'radio' => 'Группа переключателей (radio)',
						'int' => 'Целое число (int)',
						'double' => 'Дробное число (double)',
						'checkbox' => 'Флажок (checkbox)',
						'password' => 'Пароль (password)',
						),
					),
				'pd[default_value]' => array(
					'label' => 'Значение по умолчанию',
					'type' => 'text',
					'value' => $data['default_value'],
					),
				'pd[params]' => array(
					'label' => 'Параметры (yaml или ; )',
					'type' => 'textarea',
					'value' => $data['params'],
					),
				'pd[attrs]' => array(
					'label' => 'Атрибуты (yaml)',
					'type' => 'textarea',
					'value' => $data['attrs'],
					),
				'pd[validators]' => array(
					'label' => 'Валидаторы (yaml)',
					'type' => 'textarea',
					'value' => $data['validators'],
					),
				'pd[descr]' => array(
					'label' => 'Описание',
					'type' => 'textarea',
					'value' => $data['descr'],
					),
				'pd[service_comment]' => array(
					'label' => 'Служебный камент.',
					'type' => 'textarea',
					'value' => $data['service_comment'],
					),
				),
			'autofocus' => 'pd[title]',
			'buttons' => array(
				'submit[update_field]' => array(
					'type' => 'submit',
					'value' => 'Сохранить',
					),
				'submit[delete_field]' => array(
					'type' => 'submit',
					'value' => 'Удалить',
					'onclick' => "return confirm('Вы уверены, что хотите удалить поле?')",
					),
				'submit[cancel_field_edit]' => array(
					'type' => 'submit',
					'value' => 'отменить',
					),
				),
			);
	}
	
	/**
	 * Получить данные для формы редактирования веб-формы.
	 *
	 * @param array $data
	 * @return array
	 */
	public function getEditFormData($data)
	{
		return array(
			'hiddens' => array(
				'pd[form_id]' => $data['form_id'],
				),
			'elements' => array(
				'_create_datetime' => array(
					'label' => 'Создана',
					'type' => 'html',
					'value' => $data['create_datetime'] . ' (by user id: ' .$data['owner_id'] . ')',
					),
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'text',
					'value' => $data['title'],
					),
				'pd[name]' => array(
					'label' => 'Техническое имя',
					'type' => 'text',
					'value' => $data['name'],
					),
				'pd[submit_title]' => array(
					'label' => 'Надпись на кнопке',
					'type' => 'text',
					'value' => $data['submit_title'],
					),
				'pd[descr]' => array(
					'label' => 'Описание',
					'type' => 'textarea',
					'value' => $data['descr'],
					),
				'pd[success_message]' => array(
					'label' => 'Описание',
					'type' => 'textarea',
					'value' => $data['success_message'],
					),
				'pd[use_captcha]' => array(
					'label' => 'Использовать каптчу (Указать ноду)',
					'type' => 'text',
					'value' => $data['use_captcha'],
					),
				),
			'autofocus' => 'pd[title]',
			'buttons' => array(
				'submit[update_webform]' => array(
					'type' => 'submit',
					'value' => 'Сохранить',
					),
				/*'submit[cancel]' => array(
					'type' => 'submit',
					'value' => 'отменить',
					),
				*/
				),
			);
	}
	
	/**
	 * Удаление веб формы и всех связанных с нею данных.
	 *
	 * @param int $form_id
	 * @return bool
	 */
	public function deleteWebform($form_id)
	{
		$sql = "DELETE FROM {$this->DB->prefix()}webforms
			WHERE site_id = '{$this->Env->site_id}'
			AND form_id = '$form_id' ";
		$this->DB->exec($sql);
		
		$sql = "DELETE FROM {$this->DB->prefix()}webforms_fields
			WHERE site_id = '{$this->Env->site_id}'
			AND form_id = '$form_id' ";
		$this->DB->exec($sql);
		
		$sql = "DELETE FROM {$this->DB->prefix()}webforms_fields_translation
			WHERE site_id = '{$this->Env->site_id}'
			AND form_id = '$form_id' ";
		$this->DB->exec($sql);
		
		$sql = "DELETE FROM {$this->DB->prefix()}webforms_results
			WHERE site_id = '{$this->Env->site_id}'
			AND form_id = '$form_id' ";
		$this->DB->exec($sql);
		
		$sql = "DELETE FROM {$this->DB->prefix()}webforms_translation
			WHERE site_id = '{$this->Env->site_id}'
			AND form_id = '$form_id' ";
		$this->DB->exec($sql);
		return true;
	}
	
	/**
	 * Админский обработчик POST данных.
	 *
	 * @return bool
	 */
	public function adminPostProcessor()
	{
		foreach ($_POST['submit'] as $key => $value) {
			$submit = $key;
		}
		
		switch ($submit) {
			case 'create_webform':
				$this->createWebform($_POST['pd']);
				break;
			case 'update_webform':
				$this->updateWebform($_POST['pd']);
				break;
			case 'create_field':
				$this->createField($_POST['pd']);
				break;
			case 'update_field':
				$this->updateField($_POST['pd']);
				cf_redirect(HTTP_ROOT . ADMIN . '/module/WebForm/' . $_POST['pd']['form_id'] . '/fields/');
				break;
			case 'delete_field':
				$this->deleteField($_POST['pd']);
				cf_redirect(HTTP_ROOT . ADMIN . '/module/WebForm/' . $_POST['pd']['form_id'] . '/fields/');
				break;
			case 'cancel_field_edit':
				cf_redirect(HTTP_ROOT . ADMIN . '/module/WebForm/' . $_POST['pd']['form_id'] . '/fields/');
				break;
			default;
		}
		
		return true;
	}
	
	/**
	 * Обновление поля.
	 *
	 * @param array $pd
	 * @return bool
	 */
	public function updateField($pd)
	{
		$field_id	= $pd['field_id'];
		$name		= $this->DB->quote(trim($pd['name']));
		$title		= $this->DB->quote(trim($pd['title']));
		$descr		= $this->DB->quote(trim($pd['descr']));
		$validators = $this->DB->quote(trim($pd['validators']));
		$attrs		= $this->DB->quote(trim($pd['attrs']));
		$params		= $this->DB->quote(trim($pd['params']));
		$type		= $this->DB->quote(trim($pd['type']));
		$default_value = $this->DB->quote(trim($pd['default_value']));
		$service_comment = $this->DB->quote(trim($pd['service_comment']));
		
		$pos		= is_numeric($pd['pos']) ? $pd['pos'] : 0;
		$is_active	= is_numeric($pd['is_active']) ? $pd['is_active'] : 0;
		$is_required = is_numeric($pd['is_required']) ? $pd['is_required'] : 1;
		
		$sql = "
			UPDATE {$this->DB->prefix()}webforms_fields SET
				is_active = '$is_active',
				is_required = '$is_required',
				pos = '$pos',
				type = $type,
				default_value = $default_value,
				attrs = $attrs,
				params = $params,
				validators = $validators,
				name = $name,
				title = $title,
				descr = $descr,
				service_comment = $service_comment
			WHERE site_id = '{$this->Env->site_id}'
			AND field_id = '$field_id' ";
		$this->DB->query($sql);
		/*
		$sql = "
			UPDATE {$this->DB->prefix()}webforms_fields_translation SET
			WHERE site_id = '{$this->Env->site_id}'
			AND field_id = '$field_id' ";
		$this->DB->query($sql);
		*/
		return true;
	}
	
	/**
	 * Удаление поля.
	 *
	 * @param array $pd
	 * @return bool
	 */
	public function deleteField($pd)
	{
		$field_id = $pd['field_id'];
		$sql = "DELETE FROM {$this->DB->prefix()}webforms_fields
			WHERE site_id = '{$this->Env->site_id}'
			AND field_id = '$field_id' ";
		$this->DB->exec($sql);
		
		$sql = "DELETE FROM {$this->DB->prefix()}webforms_fields_translation
			WHERE site_id = '{$this->Env->site_id}'
			AND field_id = '$field_id' ";
		$this->DB->exec($sql);
		return true;
	}
	
	/**
	 * Создание поля.
	 *
	 * @param array $pd
	 * @return $field_id|false
	 */
	public function createField($pd)
	{
		if (strlen(trim($pd['name'])) == 0 or strlen(trim($pd['title'])) == 0) {
			return false;
		}
		
		$is_active	= is_numeric($pd['is_active']) ? $pd['is_active'] : 0;
		$form_id	= $pd['form_id'];
		$name		= $this->DB->quote(trim($pd['name']));
		$title		= $this->DB->quote(trim($pd['title']));
		
		$sql = "SELECT max(pos) AS max_pos
			FROM {$this->DB->prefix()}webforms_fields
			WHERE site_id = '{$this->Env->site_id}'
			AND form_id = '$form_id' ";
		$result = $this->DB->query($sql);
		$row = $result->fetchObject();
		$max_pos = (int) $row->max_pos;
		
		$sql = "
			INSERT INTO {$this->DB->prefix()}webforms_fields
				(site_id, create_datetime, owner_id, name, form_id, pos, title, is_active)
			VALUES
				('{$this->Env->site_id}', NOW(), {$this->Env->user_id}, $name, '$form_id', '$max_pos', $title, '$is_active') ";
		$this->DB->query($sql);
		$field_id = $this->DB->lastInsertId();
		return $field_id;
	}
	
	/**
	 * Обновление веб-формы.
	 *
	 * @param array $pd
	 * @return bool
	 */
	public function updateWebform($pd)
	{
		if (is_numeric($pd['form_id'])) {
			$id = $pd['form_id'];
		} else {
			return false;
		}

		$title			= $this->DB->quote(trim($pd['title']));
		$descr			= $this->DB->quote(trim($pd['descr']));
		$submit_title	= $this->DB->quote(trim($pd['submit_title']));
		$success_message= $this->DB->quote(trim($pd['success_message']));
		$name			= $this->DB->quote(trim($pd['name']));
		$use_captcha	= $this->DB->quote(trim($pd['use_captcha']));

		$sql = "
			UPDATE {$this->DB->prefix()}webforms SET
				name = $name,
				use_captcha = $use_captcha
			WHERE site_id = '{$this->Env->site_id}'
			AND form_id = '$id' ";
		$this->DB->query($sql);
		
		$sql = "
			UPDATE {$this->DB->prefix()}webforms_translation SET
				title = $title,
				descr = $descr,
				submit_title = $submit_title,
				success_message = $success_message
			WHERE site_id = '{$this->Env->site_id}'
			AND form_id = '$id'
			AND language_id = '{$this->Env->language_id}' ";
		$this->DB->query($sql);
		return true;
	}
	
	/**
	 * Создание новой веб-формы.
	 *
	 * @param array $pd
	 * @return bool
	 */
	public function createWebform($pd)
	{
		$name	= $this->DB->quote(trim($pd['name']));
		$title	= $this->DB->quote(trim($pd['title']));
		
		$sql = "
			INSERT INTO {$this->DB->prefix()}webforms
				(site_id, create_datetime, owner_id, name)
			VALUES
				('{$this->Env->site_id}', NOW(), {$this->Env->user_id}, $name) ";
		$this->DB->query($sql);
		$form_id = $this->DB->lastInsertId();
		
		$sql = "
			INSERT INTO {$this->DB->prefix()}webforms_translation
				(site_id, form_id, language_id, title, success_message)
			VALUES
				('{$this->Env->site_id}', '$form_id', '{$this->Env->language_id}', $title, 'success_message') ";
		$this->DB->query($sql);
		
		return true;
	}
	
	/**
	 * Получить данные для формы создания новой формы.
	 *
	 * @param
	 * @return array
	 */
	public function getCreateFormData()
	{
		return array(
			'hiddens' => array(
//				'action' => 'update_text_item',
//				'item_id' => $uri_path_parts[0],
				),
			'elements' => array(
				'pd[title]' => array(
					'label' => 'Заголовок',
					'type' => 'text',
					'value' => '',
					),
				'pd[name]' => array(
					'label' => 'Техническое имя',
					'type' => 'text',
					'value' => '',
					),
				),
			'autofocus' => 'pd[title]',
			'buttons' => array(
				'submit[create_webform]' => array(
					'type' => 'submit',
					'value' => 'Создать веб-форму',
					),
				),
			);
	}
	
	/**
	 * Получить параметры подключения модуля.
	 * 
	 * @return array
	 */
	public function getParams()
	{
		$tmp = $this->getWebformsList();
		
		$webforms_list = array(0 => '-');
		foreach ($tmp as $key => $value) {
			$webforms_list[$key] = $value['title'];
		}
		
		$node_params = array(
			'form_id' => array(
				'label' => 'Форма',
				'type' => 'select',
				'value' => $this->form_id,
				'options' => $webforms_list,
				),
			'css_class' => array(
				'label' => 'CSS class',
				'type' => 'text',
				'value' => $this->css_class,
				),
			'email_to' => array(
				'label' => 'Отправлять результаты на email',
				'type' => 'text',
				'value' => $this->email_to,
				),
			'email_from' => array(
				'label' => 'С какого адреса отсылать оповещения',
				'type' => 'text',
				'value' => $this->email_from,
				),
			'is_email_immediately' => array(
				'label' => 'Оповещать на емаил сразу после заполения формы',
				'type' => 'checkbox',
				'value' => $this->is_email_immediately,
				),
			'reminder_period' => array(
				'label' => 'Период для отсылки писем с напоминанием о новых ответах (в минутах)',
				'type' => 'text',
				'value' => $this->reminder_period,
				),
			);
		return $node_params;
	}

	/**
	 * Вызывается при создании ноды.
	 * 
	 * @return array $params
	 */
	public function createNode()
	{
		$this->DB->import(dirname(__FILE__) . '/sql/install', array(
			'prefix' => trim($this->DB->prefix()),
			));
		$params = parent::createNode();
		return $params;
	}
}