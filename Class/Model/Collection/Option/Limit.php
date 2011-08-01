<?php
/**
 * 
 * @desc Выбирает количество комментариев к рассказу
 * @author Goorus
 * @package Ice_Vipgeo
 * 
 */
class Model_Collection_Option_Limit extends Model_Collection_Option_Abstract
{
	

	public function before (Model_Collection $collection, 
		Query $query, array $params)
	{
		$query->limit (
			$params ['count'],
			isset ($params ['offset']) ? $params ['offset'] : null
		);
	}
	
}
