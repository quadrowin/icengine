<?php

/**
 * Запрос типа insert для mongodb
 *
 * @author morph, goorus, neon
 */
class Query_Translator_Mongo_Insert extends Query_Translator_Mongo_Select
{
	/**
	 * Рендеринг INSERT запроса.
	 *
	 * @param Query $query Запрос.
	 * @return string Сформированный SQL запрос.
	 */
	public function _renderInsert(Query_Abstract $query)
	{
		$locator = IcEngine::serviceLocator();
		$modelScheme = $locator->getService('modelScheme');
		$table = $query->part(Query::INSERT);
		$values = $query->part(Query::VALUES);
		$valuesEscaped = array();
		foreach ($values as $valueName => $value) {
			$valuesEscaped[$valueName] = (string) $value;
		}
		return array(
			'collection'	=> strtolower($modelScheme->table($table)),
			'a'				=> $valuesEscaped
		);
	}
}