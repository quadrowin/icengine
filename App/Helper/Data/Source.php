<?php

class Helper_Data_Source
{

	/**
	 * @desc Получить информацию о колонке таблицы
	 * @param string $model
	 * @param string $field
	 * @return Objective
	 */
	public static function field ($model, $field)
	{
		$query = Query::instance ()
			->show ('FULL COLUMNS')
			->from ($model)
			->where ('Field', $field);
		
		$status = DDS::executeAuto (
			$query
		)
			->getResult ()
				->asRow ();

		return new Objective ($status);
	}

	/**
	 * @desc Получить список колонок таблицы
	 * @param string $model
	 * @return Objective
	 */
	public static function fields ($model)
	{
		$query = Query::instance ()
			->show ('FULL COLUMNS')
			->from ($model);

		$status = DDS::executeAuto (
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