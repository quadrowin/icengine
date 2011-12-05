<?php

namespace Ice;

/**
 *
 * @desc Для выбора Id линка.
 * @author Юрий Шведов, Илья Колесников
 * @package Ice
 *
 */
class Link_Collection_Option_Id extends Model_Collection_Option_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Model_Collection_Option_Abstract::before()
	 */
	public function before (Model_Collection $collection,
		Query $query, array $params)
	{
		$query
			->where ('toTable=?', 		$params ['toTable'])
			->where ('toTableId=?', 	$params ['toTableId'])
			->where ('fromTable=?', 	$params ['fromTable'])
			->where ('fromTableId=?',	$params ['fromTableId']);
	}
}