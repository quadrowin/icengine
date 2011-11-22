<?php
/**
 * 
 * @desc Опция для выбора текущего баланса модели.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Collection_Option_Balance_Value extends Model_Collection_Option_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Model_Collection_Option_Abstract::before()
	 */
	public function before (Model_Collection $collection, 
		Query $query, array $params)
	{
		$model = $collection->modelName ();
		$kf = $collection->keyField ();
		
		$query
			->select ('Component_Balance.value AS balance_value')
			->singleLeftJoin (
				'Component_Balance',
				"Component_Balance.rowId=`$model`.`$kf` AND
				 Component_Balance.table='$model'"
			);
	}
	
}