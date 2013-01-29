<?php

/**
 * Транслятор запроса типа select для mongodb
 *
 * @author morph, goorus, neon
 */
class Query_Translator_Mongo_Update extends Query_Translator_Mongo_Select
{
	/**
	 * Рендеринг UPDATE запроса.
	 *
	 * @param Query $query Запрос.
	 * @return array
	 */
	public function _renderUpdate(Query_Abstract $query)
	{
		$locator = IcEngine::serviceLocator();
		$modelScheme = $locator->getService('modelScheme');
		$table = $query->part(Query::UPDATE);
		return array(
			'collection'	=> strtolower($modelScheme->table($table)),
			'criteria'		=> $this->_getCriteria($query),
			'newobj'		=> $query->part(Query::VALUES),
			'options'		=> array(
				'upsert'		=> true,
				'multi'			=> $query->part(Query::LIMIT_COUNT) != 1
			)
		);
	}
}