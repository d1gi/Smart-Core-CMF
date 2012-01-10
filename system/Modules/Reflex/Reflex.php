<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Module Reflex.
 * 
 * Применяется для вытягивания данных с других нод и отображения их в произвольном месте.
 * 
 * @uses Node
 * @uses Zend_Config_Yaml
 * 
 * @package Module
 * @version 2011-12-08.0
 */
class Module_Reflex extends Module
{
	/**
	 * id ноды, в которой запрашивается хук.
	 * @var int
	 */
	protected $hook_node_id;
	
	/**
	 * Имя метода хука.
	 * @var string
	 */
	protected $hook_method;

	/**
	 * Аргументы хука.
	 * @var yaml
	 */
	protected $hook_args;

	/**
	 * Имя шаблона для отображения данных 
	 * @var string
	 */
	protected $hook_tpl;
	
	/**
	 * Использовать массив для помещения выходных данных.
	 * 
	 * Например $this->output_data['hook_result']
	 * @var string
	 */
	protected $hook_output_data_key;
	
	/**
	 * Конструктор.
	 * 
	 * @return void
	 */
	protected function init()
	{
		$this->Node->setDefaultParams(array(
			'hook_node_id' => 0,
			'hook_method' => '',
			'hook_args' => '',
			'hook_tpl' => '',
			'hook_output_data_key' => '',
			));

		$this->hook_node_id 	= $this->Node->getParam('hook_node_id');
		$this->hook_method 		= $this->Node->getParam('hook_method');
		$this->hook_args 		= $this->Node->getParam('hook_args');
		$this->hook_tpl 		= $this->Node->getParam('hook_tpl');
		$this->hook_output_data_key = $this->Node->getParam('hook_output_data_key');
	}
	
	/**
	 * Запуск модуля.
	 * 
	 * @return void
	 */
	public function run($parser_data)
	{
		if ($this->hook_node_id == 0 or empty($this->hook_method)) {
			return;
		}
		
		if (strlen($this->hook_args) == 0) {
			$this->hook_args = null;
		} else {
			// @todo избавиться от YAML!
			$this->hook_args = Zend_Config_Yaml::decode($this->hook_args);
		}
		
		$Node = new Node($this->hook_node_id);
		
		if (empty($this->hook_output_data_key)) {
			$this->output_data = $Node->hook($this->hook_method, $this->hook_args);
		} else {
			$this->output_data[$this->hook_output_data_key] = $Node->hook($this->hook_method, $this->hook_args);
		}
		
		$this->setTpl($this->hook_tpl);
		$this->setTplPath('Modules/' . $Node->getProperties($this->hook_node_id, 'module_id') . '/');
	}	

}