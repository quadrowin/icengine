<?php

/**
 * Аксессор ссылок для схемы моделей
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Accessor_Reference extends 
    Model_Mapper_Scheme_Accessor_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Accessor_Abstract::get
	 */
	public function get($scheme, $entity)
	{
		return $entity->getValue()->data(
			$scheme->getModel()->modelName(),
			$scheme->getModel()->key()
		);
	}
}