<?php

class Helper_Data_Source
{

	/**
	 * @desc Получить информацию о колонке таблицы
	 * @param string $table
	 * @param string $field
	 * @return Objective
	 */
	public static function field ($table, $field)
	{
		$query = Query::instance ()
			->show ('FULL COLUMNS')
			->from ($table)
			->where ('Field', $field);

		$status = DDS::execute (
			$query
		)
			->getResult ()
				->asRow ();

		return new Objective ($status);
	}

	/**
	 * @desc Получить список колонок таблицы
	 * @param string $table
	 * @return Objective
	 */
	public static function fields ($table)
	{
		$query = Query::instance ()
			->show ('TABLE STATUS')
			->where ('Name', $table);

		$exists = DDS::execute ($query)
			->getResult ()->asValue ();

		if (!$exists)
		{
			return;
		}

		$query = Query::instance ()
			->show ('FULL COLUMNS')
			->from ($table);

		$status = DDS::execute (
			$query
		)
			->getResult ()
				->asTable ();

		return new Objective ($status);
	}

	/**
 	 * @desc Получить информацию по таблице
 	 * @return Objective
 	 */
	public static function table ($table)
 	{
 		$query = Query::instance ()
 			->show ('TABLE STATUS')
			->where ('Name', $table)
			->resetPart (Query::FROM);

 		$status = DDS::execute (
 			$query
 		)
			->getResult ()
				->asRow ();

 		return new Objective ($status);
 	}

	/**
	 * @desc Получить список таблиц текущей базы данных
	 * @return Objective
	 */
	public static function tables ()
	{
		$query = Query::instance ()
			->show ('TABLE STATUS')
			->resetPart (Query::FROM);

		$status = DDS::execute (
			$query
		)
			->getResult ()
				->asTable ();

		return new Objective ($status);
	}
}