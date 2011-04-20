<?php
/**
 * 
 * @desc Опция для сортировки по полю "sort".
 * Если $params ['order'] == 'desc', данные будут отсортированы в обратном
 * порядке.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Collection_Option_Active extends Model_Collection_Option_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Model_Collection_Option_Abstract::before()
	 */
	public function before (Model_Collection $collection, 
		Query $query, array $params)
	{
		if (
			isset ($params ['order']) &&
			strtoupper ($params ['order']) == 'DESC'
		)
		{
			$query->order (
				'`' . $collection->modelName () . '`.`sort` DESC'
			);
		}
		else
		{
			$query->order (
				'`' . $collection->modelName () . '`.`sort`'
			);
		}
	}
	
}