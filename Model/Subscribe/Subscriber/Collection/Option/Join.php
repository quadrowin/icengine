<?php

class Subscribe_Subscriber_Collection_Option_Join extends Model_Collection_Option_Abstract
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
			->innerJoin (
				'Subscribe_Subscriber_Join',
				'Subscribe_Subscriber_Join.Subscribe_Subscriber__id=Subscribe_Subscriber.id'
			)
			->where ('Subscribe_Subscriber_Join.Subscribe__id', $params ['id'])
			->where ('Subscribe_Subscriber.active', 1);
			
		if (!is_null ($params ['active']))
		{
			$query
				->where ('Subscribe_Subscriber_Join.active', (int) $params ['active']);
		}
	}
}