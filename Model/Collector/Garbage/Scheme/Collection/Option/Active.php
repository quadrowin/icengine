<?php

class Collector_Garbage_Scheme_Collection_Option_Active extends Model_Collection_Option_Abstract
{
	public function before (Model_Collection $items, Query $query, array $params)
	{
		$query
			->where ('Collector_Garbage_Scheme.active=1');
	}	
}