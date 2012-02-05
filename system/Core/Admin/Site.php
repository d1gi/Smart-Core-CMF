<?php
/**
 * Управление настройками сайта, в том числе и доменами.
 * 
 * @author	Artem Ryzhkov
 * @package	Kernel
 * 
 * @uses	Kernel
 * @uses	Zend_Locale
 * 
 * @version 2012-01-24.0
 */
class Admin_Site extends Site
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->View->setTpl('site');
	}

	/**
	 * Action...
	 * 
	 * @param string $uri_path
	 */
	public function run($uri_path)
	{
		if (count($_POST) > 0) {
			$this->postProcessor();
			return;
		}

		$this->View->menu = array(
			'site' => array(
				'title' => 'Основные настройки сайта',
				'uri' => '',
				'descr' => '',
				),
			'meta' => array(
				'title' => 'Мета-данные',
				'uri' => 'meta/',
				'descr' => '',
				),
			'domains' => array(
				'title' => 'Домены',
				'uri' => 'domains/',
				'descr' => '',
				),
			);
		
		$uri_path_parts = explode('/', $uri_path);
		switch ($uri_path_parts[0]) {
			case '':
				$this->View->site_edit_form = $this->getSiteEditFormData();
				$this->View->menu['site']['selected'] = true;
				break;
			case 'meta':
				$Meta = new Component_Meta($this->getMetaData());
				$this->View->meta_controls = $Meta->getControls();
				$this->View->menu['meta']['selected'] = true;
				break;
			case 'domains':
				$this->View->domains = $this->getDomainsList();
				$this->View->menu['domains']['selected'] = true;
				break;
			default;
		}
	}
	
	/**
	 * Получить список мета данных.
	 *
	 * @param int $site_id - ид сайта, по умолчанию системый.
	 * @return array
	 */
	public function getMetaData($site_id = false)
	{
		/*
		if ($site_id === false) {
			$site_id = $this->Env->site_id;
		}
		*/
		$folder = $this->Folder->getData('', 0);
		return empty($folder->meta) ? false : unserialize($folder->meta);
	}
	
	/**
	 * Получить форму редактирования сайта.
	 *
	 * @param int $site_id - ид сайта, по умолчанию системый.
	 * @return array
	 */
	public function getSiteEditFormData($site_id = false)
	{
		if ($site_id === false) {
			$site_id = $this->Env->site_id;
		}
		
		$tz = array();
		foreach (Zend_Locale::getTranslationList('CityToTimezone', 'ru') as $key => $value) {
			$tz[$key] = $key . " (" . $value . ')';
		}
		
		$properties = $this->getProperties($site_id);
		
		$themes = array();
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}engine_themes
			WHERE site_id = '$site_id' ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$themes[$row->theme_id] = array(
				'title' => $row->name . ' (' . $row->descr . ')',
				);
		}
		
		return array(
			'elements' => array(
				'pd[_create_datetime]' => array(
					'label' => 'Дата создания',
					'type' => 'string',
					'value' => $properties['create_datetime'],
					'disabled' => true,
					),
				'pd[_language_name]' => array(
					'label' => 'Основной язык',
					'type' => 'string',
					'value' => Zend_Locale::getTranslation($properties['language_id'], 'Language', 'ru'),
					'disabled' => true,
					),
				'pd[short_name]' => array(
					'label' => 'Короткое название',
					'type' => 'string',
					'value' => $properties['short_name'],
					),
				'pd[full_name]' => array(
					'label' => 'Полное название',
					'type' => 'string',
					'value' => $properties['full_name'],
					),
				'pd[multi_language]' => array(
					'label' => 'Мультиязычный режим',
					'type' => 'checkbox',
					'value' => $properties['multi_language'],
					),
				'pd[cache_enable]' => array(
					'label' => 'Вклчюить кеширование',
					'type' => 'checkbox',
					'value' => $properties['cache_enable'],
					),
				'pd[theme_id]' => array(
					'label' => 'Тема по умолчанию',
					'type' => 'select',
					'value' => $properties['theme_id'],
					'options' => $themes,
					),
				'pd[cookie_prefix]' => array(
					'label' => 'Префикс Cookie',
					'type' => 'string',
					'value' => $properties['cookie_prefix'],
					),
				'pd[session_name]' => array(
					'label' => 'session_name',
					'type' => 'string',
					'value' => $properties['session_name'],
					),
				'pd[timezone]' => array(
					'label' => 'Временная зона',
					'type' => 'select',
					'options' => $tz,
					'value' => $properties['timezone'],
					),
				'pd[robots_txt]' => array(
					'label' => 'robots.txt',
					'type' => 'textarea',
					'value' => $properties['robots_txt'],
					),
				'pd[dir_application]' => array(
					'label' => 'dir_application',
					'type' => 'text',
					'value' => $properties['dir_application'],
					),
				'pd[dir_themes]' => array(
					'label' => 'dir_themes',
					'type' => 'text',
					'value' => $properties['dir_themes'],
					),
				'pd[layouts]' => array(
					'label' => 'Макеты <br /><b>@todo Убрать!</b>',
					'type' => 'textarea',
					'value' => $properties['layouts'],
					),
				'pd[views]' => array(
					'label' => 'Представления в макетах<br /><b>@todo Убрать!</b>',
					'type' => 'textarea',
					'value' => $properties['views'],
					),
				'pd[root_layout]' => array(
					'label' => 'Вид для корневой папки',
					'type' => 'string',
					'value' => $properties['root_layout'],
					),
				'pd[root_view]' => array(
					'label' => 'Представление для корневой папки <br /><b>@todo Убрать!</b>',
					'type' => 'text',
					'value' => $properties['root_view'],
					),
				),
			'autofocus' => 'pd[short_name]',
			'buttons' => array(
				'submit[update_site]' => array(
					'value' => 'Сохранить изменения',
					'type' => 'submit',
					),
				/*
				'submit[cancel]' => array(
					'value' => 'Отменить',
					'type' => 'submit',
					),
				*/
				),
			'help' => 'Cправка по редактированию сайта',
			/*
			'fieldsets' => array(
				'site_properties' => array(
					'title' => 'Сайт',
					'elements' => 'all',
					),
				),
			*/
			);
	}

	/**
	 * Обработчик POST данных.
	 */
	public function postProcessor()
	{
		foreach ($_POST['submit'] as $key => $value) {
			$submit = $key;
		}
		
		switch ($submit) {
			case 'update_site':
				$this->update($_POST['pd']);
				break;
			case 'update_domains':
				$this->updateDomains($_POST['pd']);
				break;
			case 'create_meta':
				$this->createMetaTag($_POST['pd']);
				break;
			case 'update_meta_tags':
				$this->updateMetaTags($_POST['pd']);
				break;
			default;
		}
	}
	
	/**
	 * Обновление мета-тэгов.
	 *
	 * @param array $pd - массив данных.
	 * @param int $site_id - ид сайта, по умолчанию системый.
	 * @return bool
	 */
	public function updateMetaTags($pd, $site_id = false)
	{
		$Folder = new Folder();
		return $Folder->updateMeta(1, $pd);
	}
	
	/**
	 * Создание мета-тэга.
	 *
	 * @param array $pd - массив данных.
	 * @param int $site_id - ид сайта, по умолчанию системый.
	 * @return bool
	 */
	public function createMetaTag($pd, $site_id = false)
	{
		$Folder = new Folder();
		return $Folder->createMeta(1, $pd);
	}
	
	/**
	 * Обновление доменов.
	 *
	 * @param array $pd - массив данных.
	 * @param int $site_id - ид сайта, по умолчанию системый.
	 * @return bool
	 */
	public function updateDomains($pd, $site_id = false)
	{
		if ($site_id === false) {
			$site_id = $this->Env->site_id;
		}

		$domains_list = $this->getDomainsList();
		
		// @todo пока тупо удаляются все домены и заново пишутся новые %)) потом можно сделать интелектуальнее, сравнивая изменения и делая апдейты.
		$sql = "DELETE FROM {$this->DB->prefix()}engine_sites_domains WHERE site_id = '{$site_id}' ";
		$this->DB->exec($sql);
		
		foreach ($pd as $key => $value) {
			if ($key == '_new_domain_') {
				continue;
			}
			if ($value['delete'] == 1) {
				continue;
			}
			if ($_SERVER['HTTP_HOST'] == $key) {
				$domain = "'$key'";
			} else {
				$domain = $this->DB->quote(trim($value['domain']));
			}
			
			$descr = $this->DB->quote(trim($value['descr']));
			$create_datetime = $domains_list[$key]['create_datetime'];
			
			$sql = "
				INSERT INTO {$this->DB->prefix()}engine_sites_domains
					(domain, site_id, descr, create_datetime )
				VALUES
					($domain, '{$site_id}', $descr, '$create_datetime' ) ";
			$this->DB->query($sql);
		}
		if (!empty($pd['_new_domain_']['domain'])) {
			$domain	= $this->DB->quote(trim($pd['_new_domain_']['domain']));
			$descr	= $this->DB->quote(trim($pd['_new_domain_']['descr']));
			
			$sql = "
				INSERT INTO {$this->DB->prefix()}engine_sites_domains
					(domain, site_id, descr, create_datetime )
				VALUES
					($domain, '{$this->Env->site_id}', $descr, NOW() ) ";
			$this->DB->query($sql);
		}
	
		return true;
	}
	
	/**
	 * Обновление сайта.
	 *
	 * @param array $pd - массив данных.
	 * @param int $site_id - ид сайта, по умолчанию системый.
	 * @return bool
	 */
	public function update($pd, $site_id = false)
	{
		if ($site_id === false) {
			$site_id = $this->Env->site_id;
		}
		
		$theme_id = $this->DB->quote(trim($pd['theme_id']));
			
		$properties = array(
			'short_name'	=> trim($pd['short_name']),
			'full_name'		=> trim($pd['full_name']),
			'timezone'		=> trim($pd['timezone']),
			'cookie_prefix'	=> trim($pd['cookie_prefix']),
			'session_name'	=> trim($pd['session_name']),
			'robots_txt'	=> trim($pd['robots_txt']),
			'multi_language'	=> trim($pd['multi_language']),
			'cache_enable'	=> trim($pd['cache_enable']),
			'dir_application'	=> trim($pd['dir_application']),
			'dir_themes'	=> trim($pd['dir_themes']),
			'root_layout'	=> trim($pd['root_layout']),
			'root_view'		=> trim($pd['root_view']),
			'layouts'		=> trim($pd['layouts']),
			'views'			=> trim($pd['views']),
			);
		
		$sql = "
			UPDATE {$this->DB->prefix()}engine_sites SET
				theme_id = $theme_id,
				properties = {$this->DB->quote(serialize($properties))}
			WHERE site_id = '{$site_id}' ";
		$this->DB->query($sql);
		return true;
	}
}