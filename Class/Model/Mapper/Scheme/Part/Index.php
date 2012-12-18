<?php

/**
 * Часть схемы моделей, отвечающая за индексы
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Part_Index extends Model_Mapper_Scheme_Part_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Part_Abstract::$_specification
	 */
	protected static $specification = 'indexes';

	/**
	 * Создать индекс схемы модели
	 * 
     * @param string $name Название поля
	 * @param mixed fields Атрибуты
	 * @return Model_Mapper_Scheme_Index_Abstract
	 */
	public function set($name, $fields)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $schemeIndex = $serviceLocator->getService('modelMapperSchemeIndex');
		$index = $schemeIndex->byName($name);
		$index->setFields((array) $fields);
		return $index;
	}

	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Part_Abstract::execute
	 */
	public function execute($scheme, $values)
	{
		foreach ($values as $name => $params) {
			$fields = array();
			if (!empty($params[1])) {
				$fields = $params[1] ? $params[1]->__toArray () : array();
			}
			$indexName = $name . '_index';
			$scheme->$indexName = $this->set($params[0], $fields);
		}
		return $scheme;
	}
}