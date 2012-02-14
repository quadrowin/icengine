<?php

namespace Ice;

/**
 *
 * @desc Опция для выбора текущего баланса модели.
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Model_Option_Balance_Value extends Model_Option
{

	/**
	 * (non-PHPdoc)
	 * @see Model_Option::before()
	 */
	public function before ()
	{
		$model = $this->collection->modelName ();
		$kf = $this->collection->keyField ();

		$this->query
			->select ('Balance.value AS balance_value')
			->singleLeftJoin (
				'Balance',
				"Balance.rowId=`$model`.`$kf` AND
				 Balance.table='$model'"
			);
	}

}