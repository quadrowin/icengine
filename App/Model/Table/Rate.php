<?php

namespace Ice;

/**
 *
 * @desc Рейтинг таблиц
 * Рейтинг ведется в соответвии с частотой обращений к таблице
 * из универсальной админки.
 * @author Ilya Kolesnikov
 * @package Ice
 *
 */
class Table_Rate extends Model
{
	/**
	 * @desc Получить модель по таблице
	 * @param string $table
	 * @return Table_Rage
	 */
	public static function byTable ($table)
	{
		$rate = Model_Manager::getInstance ()->byQuery (
			'Table_Rate',
			Query::instance ()
				->where ('table', $table)
		);

		if (!$rate)
		{
			$rate = new self (array (
				'table'	=> $table,
				'value' => 0
			));
		}

		return $rate;
	}

	/**
	 * @desc Инкрементировать рейтинг
	 */
	public function inc ()
	{
		$this->value++;
		$this->save ();
	}
}