<?php

/**
 * Тип ссылки "один-ко-многим"
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Reference_OneToMany extends 
    Model_Mapper_Scheme_Reference_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data($modelName, $id)
	{
		$field = $this->getField();
        $serviceLocator = IcEngine::serviceLocator();
        $modelScheme = $serviceLocator->getService('modelScheme');
        $keyField = $modelScheme->keyField($modelName);
		if (!$field) {
			$field = $modelName . '__' . $keyField;
		}
        $collectionManager = $serviceLocator->getService('collectionManager');
        $queryBuilder = $serviceLocator->getService('query');
        $query = $queryBuilder->where($this->getModel() . '.' . $field, $id);
        $collection = $collectionManager->byQuery($this->getModel(), $query);
		return $this->resource()->setItems($collection->items());
	}
}