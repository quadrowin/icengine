<?php

class Data_Mapper_Defined extends Data_Mapper_Abstract
{
	public function execute (Data_Source_Abstract $source, Query $query, $options = null)
	{
		$select = $query->getPart (Query::SELECT);
		
		$model_name = reset ($select);
		
		if (is_array ($model_name))
		{
			$model_name = reset ($model_name);
		}
		else
		{
			return new Query_Result (array (
				'source'	=> $source,
				'result'	=> array ()
			));
		}
		
		
		Loader::load ($model_name);
		
		$where = $query->getPart (Query::WHERE);
		
		$part = reset ($where);
		
		$id = $part [Query::VALUE];

		$model = new $model_name;
		
		$rows = $model::$_rows;   
		
		$result = array (array_merge (
			array ($part [Query::WHERE]	=> $id),
			$rows [$id]
		));
		
		return new Query_Result (array (
			'result'	=> $result,	
			'source'	=> $source
		));
	}
}