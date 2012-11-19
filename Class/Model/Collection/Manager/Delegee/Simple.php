<?php

/**
 * Базовый загрузчик для менеджера коллекций
 *
 * @author morph
 */
class Model_Collection_Manager_Delegee_Simple
{
    /**
     * Загружает коллекцию
     *
     * @param Model_Collection $collection
     * @param Query_Abstract $query
     * @return array
     */
	public static function load(Model_Collection $collection,
        Query_Abstract $query)
	{
        // Выполняем запрос, получаем элементы коллеции
		$modelName = $collection->modelName();
        $dataSource = Model_Scheme::dataSource($modelName);
        $queryResult = $dataSource->execute($query)->getResult();
		$collection->queryResult($queryResult);
		// Если установлен флаг CALC_FOUND_ROWS,
		// то назначаем ему значение
		if ($query->getPart(Query::CALC_FOUND_ROWS)) {
			$collection->data('foundRows', $queryResult->foundRows());
		}
		$scheme = Model_Scheme::scheme($modelName);
		$schemeFields = array_keys($scheme['fields']->__toArray());
		$rows = $queryResult->asTable();
        if (!$rows) {
            return array('items' => array());
        }
        $currentFields = array_keys($rows[0]);
        $needleFields = array_intersect($schemeFields, $currentFields);
        $addictFields = array_intersect($currentFields, $schemeFields);
        $items = Helper_Array::column($rows, $needleFields);
        $addicts = Helper_Array::column($rows, $addictFields);
		$collection->data('addicts', $addicts);
		return array('items' => $items);
	}
}