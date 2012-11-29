<?php

/**
 * Запросы на удаление
 *
 * @author neon
 */
class Unit_Of_Work_Query_Delete extends Unit_Of_Work_Query_Abstract
{
	/**
	 * @inheritdoc
	 *
	 * @param string $key
	 * @param array $data
	 * @return array
	 */
	public function build($key, $data)
	{
		$modelName = $key;
		$keyField = null;
		$keys = array();
		foreach ($data as $where) {
			if (!$keyField) {
				$keyField = implode('', array_keys($where['wheres']));
			}
			$keys[] = $where['wheres'][$keyField];
		}
		if (!$keys) {
			return;
		}
		$query = Query::instance()
			->delete()
			->from($modelName)
			->where($keyField, $keys);
		return array(
			'modelName'	=> $modelName,
			'query'		=> $query
		);
	}

	/**
	 * @inheritdoc
	 *
	 * @param Query_Abstract $query
	 * @param Model $object
	 * @param string $loaderName
	 * @return void
	 */
	public function push(Query_Abstract $query, $object = null, $loaderName = null)
	{
		$from = array_keys($query->getPart(QUERY::FROM));
		$table = $from[0];
		$where = $query->getPart(QUERY::WHERE)?: false;
		$wheres = array();
		if ($where) {
			foreach ($where as $value) {
				$wheres[$value[QUERY::WHERE]] = $value[QUERY::VALUE];
			}
		}
		$uniqName = $table;
		Unit_Of_Work::pushRaw(QUERY::DELETE, $uniqName, array(
			'wheres'	=> $wheres
		));
	}
}