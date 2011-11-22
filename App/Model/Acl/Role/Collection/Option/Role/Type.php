<?php

class Acl_Role_Collection_Option_Role_Type extends Model_Collection_Option_Abstract
{
	/**
	 * 
	 * @param Model_Collection $items
	 * @param Query $query
	 * @param array $params
	 */
	public function before (Model_Collection $items, Query $query, array $params)
	{
		$role_type_id = !empty ($params ['role_type_id']) ?
			$params ['role_type_id'] : 0;
			
		$query	
			->where ('Acl_Role.Acl_Role_Type__id=?', $role_type_id);
	}
}