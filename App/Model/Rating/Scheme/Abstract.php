<?php

namespace Ice;

/**
 *
 * @desc Базовая схема рейтинга.
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Rating_Scheme_Abstract extends Model_Factory_Delegate
{

	/**
	 * @desc Изменение рейтинга
	 * @param string $table Модель
	 * @param integer $row_id Запись
	 * @param integer $value Изменение рейтинга.
	 * Может быть величиной изменения или типом, в зависимости от схемы.
	 * @return Rating
	 */
	public function vote ($table, $row_id, $value)
	{
		$rating = $this->_getModelManager ()->byQuery (
			'Rating',
			Query::instance ()
				->where ('table', $table)
				->where ('rowId', $row_id)
		);

		return $rating->increment ($value);
	}

}