<?php

namespace Ice;

class Component_Comment_Collection_Option_Link extends Model_Collection_Option_Abstract
{

	/**
	 *
	 * @param Model_Collection $items
	 * @param Query $query
	 * @param array $params
	 */
	public function before (Model_Collection $items, Query $query, array $params)
	{
		$query
			->where ('table', $params ['table'])
			->where ('rowId', $params ['rowId']);
	}

}