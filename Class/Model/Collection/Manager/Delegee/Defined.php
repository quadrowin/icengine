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
		// Выполняем запрос, получаем элементы коллеции
		$query_result = 
			Model_Scheme::dataSource ($model)
				->execute ($query)
					->getResult ();

		$collection->query ($query_result);

		// Если установлен флаг CALC_FOUND_ROWS,
		// то назначаем ему значение
		if ($query->getPart (Query::CALC_FOUND_ROWS))
		{
			$collection->data ('foundRows', $query_result->foundRows ());
		}

		Loader::load ('Helper_Data_Source');

		$fields = Helper_Data_Source::fields ($collection->table ())
			->column ('Field');

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