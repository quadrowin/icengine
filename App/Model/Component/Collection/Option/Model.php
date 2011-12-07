<?php

namespace Ice;

/**
 *
 * @desc Опция для выбора компонент по модели.
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Component_Collection_Option_Model extends Model_Collection_Option_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Model_Collection_Option_Abstract::before()
	 * @param string $table Название таблицы.
	 * @param string $row_id Первичный ключ модели.
	 * @param Model $model Вместо названия таблицы и ПК может быть передана
	 * сама модель.
	 */
	public function before (Model_Collection $collection,
		Query $query, array $params)
	{
		if (isset ($params ['model']))
		{
			$query
				->where ('table', $params ['model']->modelName ())
				->where ('rowId', $params ['model']->key ());
		}
		else
		{
			$query
				->where ('table', $params ['table'])
				->where ('rowId', $params ['row_id']);
		}
	}

}