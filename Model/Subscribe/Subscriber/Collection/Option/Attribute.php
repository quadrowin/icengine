<?php

class Subscribe_Subscriber_Collection_Option_Attribute extends
	Model_Collection_Option_Abstract
{
	/**
	 * 
	 * (non-PHPDoc)
	 * @see Model_Collection_Option_Abstract::before
	 */
	public function before (Model_Collection $items, Query $query, array $params)
	{
		$query
			->innerJoin (
				'Subscribe_Subscriber_Attribute',
				'Subscribe_Subscriber_Attribute.Subscribe_Subscriber__id=
					Subscribe_Subscriber.id'
			)
			->where ('Subscribe_Subscriber_Attribute.key', $params ['key'])
			->where ('Subscribe_Subscriber_Attribute.value', $params ['value']);
	}
}