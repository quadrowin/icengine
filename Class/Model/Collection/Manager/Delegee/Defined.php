<?php
/**
 * 
 * @desc Менеджер коллекций для моделей с определенными данными
 * 
 */
class Model_Collection_Manager_Delegee_Defined
{
	
	public static function load (Model_Collection $collection, Query $query)
	{
		$model_name = $collection->modelName ();
		
		Loader::load ($model_name);

		$rows = $model_name::$rows;
		$collection->reset ();

		foreach ($rows as $row)
		{
			$collection->add (new $model_name ($row));
		}
		
		$where = $query->getPart (Query::WHERE);
		
		$filter = array ();
		
		foreach ($where as $w)
		{
			$field = rtrim ($w [Query::WHERE], '?');
			
			$filter [$field] = $w [Query::VALUE]; 
		}

		$order = $query->getPart (Query::ORDER);
		
		$sort = array ();
		
		foreach ($order as $o)
		{
			$sort [] = $o [0];	
		}
		
		$collection = $collection
			->filter ($filter)
			->sort (implode (',', $sort));
		
		$items = array ();
		
		foreach ($collection as $item)
		{
			$items [] = $item ['id'];
		}
		
		return array (
			'items'	=> $items,
		);
	}
	
}