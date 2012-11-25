<?php

/**
 * По сути mysql всё
 *
 * @author neon
 */
class Unit_Of_Work_Query_Insert extends Unit_Of_Work_Query_Abstract
{
	/**
	 * @inheritdoc
	 *
	 * @param string $key
	 * @param array $data
	 */
	public function build($key, $data)
	{
		$dataValues = $data[0]['values'];
		$part = explode('@', $key);
		$modelName = $part[0];
		/**
		 * Пока так
		 */
		$values = array();
		foreach ($data as $key=>$dataValues) {
			foreach ($dataValues['values'] as $valueKey=>$value) {
				$values[$key][$valueKey] = $value;
			}
		}
		$valuesQuery = array();
		foreach ($values as $v) {
			$valuesQuery[] = '(' . implode(',', $v) . ')';
		}
		$query = Query::instance()
			->insert($modelName);
		foreach ($values as $valuesPart) {
			$query->values($valuesPart, true);
		}
		return array(
			'modelName'	=> $modelName,
			'query'		=> $query
		);
	}

	private function prepare()
	{

	}

	/**
	 * @inheritdoc
	 * @param Query_Abstract $query
	 */
	public function push(Query_Abstract $query, $object = null, $loaderName = null)
	{
		$table = $query->getPart(QUERY::INSERT);
		$tableScheme = Config_Manager::get('Model_Mapper_' . $table);
		$tableFields = array_keys($tableScheme->fields->asArray());
		$values = $query->getPart(QUERY::VALUES);
		$resultFields = array();
		foreach ($values as $key=>$value) {
			if (in_array($key, $tableFields)) {
				$resultFields[$key] = $value;
			}
		}
		$dataFields = array_keys($resultFields);
		$uniqName = $table . '@' . md5(implode('', $dataFields));
		Unit_Of_Work::pushRaw(QUERY::INSERT, $uniqName, array(
			'values'	=> $resultFields
		));
	}
}