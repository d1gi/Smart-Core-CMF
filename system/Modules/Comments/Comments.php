<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Module Comments.
 * 
 * @uses Node
 * 
 * @package Module
 * @version 2011-07-09.0
 */
class Module_Comments extends Module
{
	/**
	 * ИД ноды, к которой подключаются комментарии
	 * @var int
	 */
	protected $source_node_id;
	
	/**
	 * 
	 * @var bool
	 */
	protected $is_only_authorized;
	
	/**
	 * Конструктор.
	 * 
	 * @return void
	 */
	protected function init()
	{
		$this->source_node_id		= $this->Node->params['source_node_id'];
		$this->is_only_authorized	= $this->Node->params['is_only_authorized'];
	}
	
	/**
	 * Запуск модуля.
	 * 
	 * @return void
	 */
	public function run($parser_data)
	{
		if ($this->source_node_id != 0) {
			$Node = new Node($this->source_node_id);
			$post_id = $Node->hook('getUniqueId');
			if (!empty($post_id)) {
				$form_data = array(
					'hiddens' => array(
						'node_id' => $this->Node->id,
						'pd[post_id]' => $post_id,
						),
					'elements' => array(
						'pd[nickname]' => array(
							'label' => 'Имя (обязательно)',
							'type' => 'string',
							'value' => '',
							),
						'pd[content]' => array(
							'type' => 'textarea',
							'value' => '',
							'style' => 'width:92%;',
							),
						),
					'buttons' => array(
						'submit[send]' => array(
							'type' => 'submit',
							'value' => 'Отправить комментарий',
							),
						),
					);
				$this->output_data['add_comment_form_data'] = $form_data;
				$this->output_data['comments'] = $this->getList($post_id);
			}
			
		}
	}
	
	/**
	 * Получить список всего комментариев к указанной записи.
	 *
	 * @param int $item_id
	 * @return array
	 */
	public function getList($post_id)
	{
		$data = array();
	
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}comments
			WHERE site_id = '{$this->Env->site_id}'
			AND post_id = '$post_id'
			AND is_active = 1
			ORDER BY comment_id ASC ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$data[$row->comment_id] = array(
				'nickname' => $row->user_nickname,
				'create_datetime' => $row->create_datetime,
				'content' => $row->content,
				);
		}
		
		return $data;
	}

	/**
	 * Создание комментария
	 *
	 * @param
	 * @return
	 */
	public function create($pd)
	{
		if (!is_numeric($pd['post_id'])) {
			return null;
		}
		
		$sql = "
			INSERT INTO {$this->DB->prefix()}comments
				(site_id, node_id, pid, post_id, is_active, status, user_id, 
				 user_nickname, user_email, user_ip, user_agent, create_datetime, content)
			VALUES
				('{$this->Env->site_id}', '{$this->source_node_id}', '0', '{$pd['post_id']}', '1', NULL, '0', 
				 {$this->DB->quote($pd['nickname'])}, '', '', '', NOW(), {$this->DB->quote($pd['content'])} ) ";
		$this->DB->query($sql);
		
		return true;
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
				$this->create($pd);
				break;
			default:
		}
	} 
	
}