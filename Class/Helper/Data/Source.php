<?php

/**
 * Помощник для Data Source
 *
 * @author ..., neon
 * @Service("helperDataSource")
 */
class Helper_Data_Source
{
	/**
	 * Получить информацию о колонке таблицы
	 *
	 * @param string $table
	 * @param string $field
	 * @return Objective
	 */
	public static function field($table, $field, $ds = null)
	{
		$dds = IcEngine::serviceLocator()->getService('dds');
		$query_builder = IcEngine::serviceLocator()->getService('query');
        if (!$ds) {
            $ds = $dds->getDataSource();
        }

		$query = $query_builder->instance ()
			->show ('FULL COLUMNS')
			->from ($table)
			->where ('Field', $field);

		$status = $ds->execute (
			$query
		)
			->getResult ()
				->asRow ();

		return new Objective ($status);
	}

	/**
	 * Получить список колонок таблицы
	 *
	 * @param string $table
	 * @return Objective
	 */
	public function fields($table, $ds = null)
	{
		$locator = IcEngine::serviceLocator();
		$dds = $locator->getService('dds');
		$queryBuilder = $locator->getService('query');
        if (!$ds) {
            $ds = $dds->getDataSource();
        }
		$query = $queryBuilder->show('FULL COLUMNS')
			->from($table);
		$status = $ds->execute($query)->getResult()->asTable();
		if (!$status) {
			return;
		}
		return new Objective($status);
	}

	/**
 	 * Получить информацию по таблице
	 *
 	 * @return Objective
 	 */
	public static function table($table, $ds = null)
 	{
		$locator = IcEngine::serviceLocator();
		$dds = $locator->getService('dds');
		$queryBuilder = $locator->getService('query');
        if (!$ds) {
            $ds = $dds->getDataSource();
        }
 		$query = $queryBuilder->show('TABLE STATUS')
			->where('Name', $table)
			->resetPart(Query::FROM);
 		$status = $ds->execute($query)->getResult()->asRow();
		if (!$status) {
			return;
		}
 		return new Objective($status);
 	}

	/**
	 * @desc Получить список таблиц текущей базы данных
	 * @return Objective
	 */
	public function tables ($ds = null)
	{
		$locator = IcEngine::serviceLocator();
		$query_builder = $locator->getService('query');
        if (!$ds) {
            $ds = $locator->getService('dds')->getDataSource();
        }

		$query = $query_builder->instance ()
			->show ('TABLE STATUS')
			->resetPart (Query::FROM);

		$status = $ds->execute (
			$query
		)
			->getResult ()
				->asTable ();

		return new Objective ($status);
	}
}