<?php

/**
 * Часть схемы моделей, отвечающая за ссылки
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Part_Reference extends 
    Model_Mapper_Scheme_Part_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Part_Abstract::$_specification
	 */
	protected static $specification = 'references';

	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Part_Abstract::set
	 */
	public function set($name, $model, $field)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $schemeReference = $serviceLocator->getService(
            'modelMapperSchemeReference'
        );
		$reference = $schemeReference->byName($name);
		$reference->setModel($model);
		$reference->setField($field);
		return $reference;
	}

	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Part_Abstract::execute
	 */
	public function execute($scheme, $values)
	{
		foreach ($values as $name => $params) {
			$scheme->$name = $this->set(
				$params[0],
				$params[1],
				isset($params[2]) ? $params[2] : null
			);
		}
		return $scheme;
	}
}