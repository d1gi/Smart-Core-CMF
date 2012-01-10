<?php
/* vim: set noexpandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Класс по работе с группами пользователей.
 * 
 * @author		Artem Ryzhkov
 * @copyright	Copyright &copy; 2010-2011 Smart Core CMF
 * @link		http://smart-core.org/
 * @license		http://opensource.org/licenses/gpl-2.0
 * 
 * @uses		DB
 * 
 * @version 	2011-11-05.0
 */
class User_Groups extends SingletonBase
{
	private $groups;
	private $_tmp_group_inherit;
	private $_tmp_group_level; // используется для указания приоритета (или веса) группы.

	/**
	 * Constructor.
	 *
	 * @param void
	 * @return void
	 */
	protected function __construct()
	{
		parent::__construct();
		$this->groups = array();
		$this->groups[0] = 'guest';
	}

	/**
	 * NewFunction
	 *
	 * @param int $user_id
	 */
	public function setUserId($user_id)
	{
		$this->groups = $this->getList($user_id);
	}
	
	/**
	* Получить список груп в виде ГРУППИРОВАННОГО массива, в которые включен юзер.
	* 
	* @todo сделать :) возможно надо будет переименовать... так же надо сделать мультияз
	* 
	* @todo зачем раньше я делал массив групп группированный по уровням?
	* 
	* @return array
	*/
	public function getGrouppedArray($user_id)
	{
		$this->_tmp_group_inherit = array();
		$this->_tmp_group_level = 0;

		$sql = "
			SELECT 
				g.name,
				g.descr,
				gr.group_id
			FROM {$this->DB->prefix()}users_groups_relation AS gr,
				 {$this->DB->prefix()}users_groups AS g
			WHERE g.group_id = gr.group_id
			AND g.site_id = '{$this->Env->site_id}'
			AND gr.site_id = '{$this->Env->site_id}'
			AND gr.user_id = '$user_id' ";
		$result = $this->DB->query($sql);
		while($row = $result->fetchObject()) {
			$this->_tmp_group_inherit[$this->_tmp_group_level][$row->group_id] = $row->name;
			$this->getGroupsInheritance($row->group_id);
		}
		
		return $this->_tmp_group_inherit;
	}
	
	/**
	* Получить список груп в виде ПРОСТОГО массива, в которые включен юзер.  
	* 
	*/
	public function getList($user_id = null)
	{
		
		$list = $this->Session->getUserGroups();
		
		if (!empty($list)) {
			return $list;
		}
		
		$list = array();
		
		$tmp = $this->getGrouppedArray($user_id);
		foreach ($tmp as $key => $value) {
			foreach ($tmp[$key] as $key2 => $value2) {
				$list[$key2] = $value2;
			}
		}
		return $list;
	}
	
	/**
	* Получить информацию о группе (какие группы она включает)
	* 
	* @return array
	*/
	public function getGroupInfo($group_id)
	{
		$this->_tmp_group_inherit = array();
		$this->_tmp_group_level = 1;
		$this->getGroupsInheritance($group_id);
		return $this->_tmp_group_inherit;
	}	
	
	/**
	 * Получить наследованный список групп включенных в группу.
	 * 
	 * @param int $group_id
	 */
	private function getGroupsInheritance($group_id)
	{
		$this->_tmp_group_level++;
		$sql = "SELECT 
				gi.group_id,
				gi.parent_group_id,
				g.name
			FROM {$this->DB->prefix()}users_groups_includes AS gi,
				 {$this->DB->prefix()}users_groups AS g 
			WHERE gi.group_id = '$group_id'
			AND g.group_id = gi.parent_group_id
			AND g.site_id = '{$this->Env->site_id}'
			AND gi.site_id = '{$this->Env->site_id}' ";
		$result = $this->DB->query($sql);
		while($row = $result->fetchObject()) {
			$this->_tmp_group_inherit[$this->_tmp_group_level][$row->parent_group_id] = $row->name;
			$this->getGroupsInheritance($row->parent_group_id);
		}
		$this->_tmp_group_level--;
	}	
	
	/**
	 * Получить список групп.
	 *
	 * @param
	 * @return array
	 */
	public function getGroupsList()
	{
		$list = array();
		
		$sql = "SELECT * 
			FROM {$this->DB->prefix()}users_groups
			WHERE site_id = '{$this->Env->site_id}'
			ORDER BY pos  ";
		$result = $this->DB->query($sql);
		while ($row = $result->fetchObject()) {
			$this->_tmp_group_inherit = array();
			$this->_tmp_group_level = 0;
			
			$this->getGroupsInheritance($row->group_id);
			
			$list[$row->group_id] = array(
				'name' => $row->name,
				'descr' => $row->descr,
				'includes' => $this->_tmp_group_inherit,
				);
		}

		return $list;
	}
	
	/**
	* @todo видимо надо переделать ;))
	* 
	*/
	public function getCurrentUsersGroupList()
	{
		return $this->groups;
	}
	
	/**
	 * Обновление юзера.
	 *
	 * @todo сейчас пока сохраняются только группы... 
	 * 
	 * @param array $data
	 * @return bool
	 */
	public function update($data)
	{
		$groups = $this->getGroupsList();
		$groups_includes = array();
		
		foreach ($data['groups'] as $group_id => $is_enable) {
			if ($is_enable) {
				$groups_includes[$group_id] = $this->getGroupInfo($group_id);
			}
		}
		
		$groups_includes_final = $groups_includes;
		
		foreach ($groups_includes as $group_id => $value) {
			foreach ($value as $g) {
				foreach ($g as $gkey => $gvalue) {
					unset($groups_includes_final[$gkey]);
				}
			}
			
		}
		
		$sql = "DELETE FROM users_groups_relation
			WHERE site_id = '{$this->Env->site_id}'
			AND user_id = '{$data['user_id']}' ";
		$this->DB->exec($sql);
		foreach ($groups_includes_final as $key => $value) {
			$sql = "
				INSERT INTO users_groups_relation
					(site_id, user_id, group_id )
				VALUES
					('{$this->Env->site_id}', '{$data['user_id']}', '{$key}' ) ";
			$this->DB->query($sql);
		}
		
		return true;
	}
}