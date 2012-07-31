<?php
/**
 *
 * @desc Опция для отсеивания по id.
 * Ожадаются параметры $ids с массивом первичных ключей или $id с
 * единичным первичным ключом
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Collection_Option_Not_Id extends Model_Collection_Option_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Model_Collection_Option_Abstract::before()
	 */
	public function before (Model_Collection $collection,
		Query $query, array $params)
	{
		if (isset ($params ['ids']) && $params ['ids'])
		{
			$query->where (
				$collection->modelName () . '.id NOT IN (?)',
				array ($params ['ids'])
			);
		}
		if (isset ($params ['id']) && $params ['id'])
		{
			$query->where (
				$collection->modelName () . '.id != ?',
				$params ['id']
			);
		}
	}

}