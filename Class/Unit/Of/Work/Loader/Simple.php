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
		//print_r($results);die;
		list($modelName, $keyField) = explode('@', $uniqName);
		$keyField = Model_Scheme::keyField($modelName);
		$resultArray = array();
		foreach ($results as $result) {
			$resultArray[$result[$keyField]] = $result;
		}
		$config = Config_Manager::get('Model_Mapper_' . $modelName);
		$fields = array();
		if (file_exists(IcEngine::root() . 'Ice/Config/Model/Mapper/' .
			str_replace('_', '/', $modelName) . '.php')) {
			$fields = array_keys($config->fields->asArray());
		} else {
			$parts = explode('@', $uniqName);
			$fields = array_values(explode(':', $parts[1]));
		}
		//print_r($fields);die;
		foreach ($raws as $raw) {
			$object = &$raw['object'];
			$fieldsPrepared = array();
			foreach ($fields as $field) {
				if (isset($resultArray[$object->key()][$field])) {
					$fieldsPrepared[$field] = $resultArray[$object->key()][$field];
				} else {
					$fieldsPrepared[$field] = strrpos($field, '__id') ? 0 : null;
				}
			}
			$object->set($fieldsPrepared);
			$object->setLoaded(true);
			Resource_Manager::set (
				'Model',
				$modelName . '__' . $object->key(),
				$object
			);
		}
	}
}