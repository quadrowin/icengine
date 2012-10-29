<?php

/**
 * Загрузчик для Unit_Of_Work по умолчанию
 * Пока что в нём только byKey {id: }
 *
 * @author neon
 */
class Unit_Of_Work_Loader_Simple extends Unit_Of_Work_Loader_Abstract
{
	/**
	 * Простая загрузка
	 *
	 * @inheritdoc
	 * @param string $uniqName
	 * @param array $raw
	 */
	public function load($uniqName, $result)
	{
		$rawData = Unit_Of_Work::getRaw(QUERY::SELECT, $uniqName);
		reset($rawData);
		$raws = current($rawData);
		$results = $result->getResult()->asTable();
		list($modelName, $keyField) = explode('@', $uniqName);
		$resultArray = array();
		foreach ($results as $result) {
			$resultArray[$result[$keyField]] = $result;
		}
		foreach ($raws as $raw) {
			$object = &$raw['object'];
			$object->set($resultArray[$object->key()]);
			Resource_Manager::set (
				'Model',
				$modelName . '__' . $object->key(),
				$object
			);
		}
	}
}