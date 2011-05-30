<?php

class Component_Comment_Collection_Option_Range extends Model_Collection_Option_Abstract
{
	/**
	 * 
	 * @param Model_Collection $items
	 * @param Query $query
	 * @param array $params
	 */
	public function before (Model_Collection $items, Query $query, array $params)
	{
		$st = !empty ($params ['st']) ? $params ['st'] : 0;
		$count = !empty ($params ['count']) ? $params ['count'] : 0;
		
		$query
			->limit ($count, $st);
	}
}