<?php

class Link_Collection_Option extends Model_Collection_Option
{
	/**
	 * 
	 * @param Model_Collection $item
	 * @param Query $query
	 * @param array $params
	 */
	public function id_before (Model_Collection $item, Query $query, array $params)
	{
		$query
			->where ('toTable=?', 		$params ['toTable'])
			->where ('toTableId=?', 	$params ['toTableId'])
			->where ('fromTable=?', 	$params ['fromTable'])
			->where ('fromTableId=?',	$params ['fromTableId']);
	}
}