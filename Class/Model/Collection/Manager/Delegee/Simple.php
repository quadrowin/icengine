<?php
/**
 * 
 * @desc Базовый загрузчик для менеджера коллекций.
 * 
 */
class Model_Collection_Manager_Delegee_Simple
{
	
	public static function load (Model_Collection $collection, Query $query)
	{
		$model = $collection->modelName ();
		
		// Выполняем запрос, получаем элементы коллеции
		$query_result = 
			Model_Scheme::dataSource ($model)
				->execute ($query)
					->getResult ();

		$collection->queryResult ($query_result);

		// Если установлен флаг CALC_FOUND_ROWS,
		// то назначаем ему значение
		if ($query->getPart (Query::CALC_FOUND_ROWS))
		{
			$collection->data ('foundRows', $query_result->foundRows ());
		}

		Loader::load ('Helper_Data_Source');

		$scheme = Model_Scheme::getScheme ($model);
		
		$fields = array_keys ($scheme ['fields']);

		$table = $query_result->asTable ();

		$key_field = Model_Scheme::keyField ($model);

		$items = array ();
		$addicts = array ();

		foreach ($table as $i => $item)
		{
			foreach ($item as $field=>$value)
			{
				if (!in_array ($field, $fields))
				{
					$addicts [$i][$field] = $value;
				}	
			}
			$items [] = $item [$key_field];
		}

		$collection->data ('addicts', $addicts);

		return array (
			'items'	=> $items,
		);
	}
	
}