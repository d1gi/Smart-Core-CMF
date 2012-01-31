<?php
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
	 * Например $this->View->hook_result
	 * @var string
	 */
	protected $hook_output_data_key;
	
	/**
	 * Конструктор.
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
	 */
	public function run($params)
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
			foreach ($Node->hook($this->hook_method, $this->hook_args) as $key => $value) {
				$this->View->$key = $value;
			}
		} else {
			$this->View->set($this->hook_output_data_key, $Node->hook($this->hook_method, $this->hook_args));
		}
		
		$this->View->setTpl($this->hook_tpl);
		$this->View->setTplPath('Modules/' . $Node->getProperties($this->hook_node_id, 'module_id') . '/');
	}
}