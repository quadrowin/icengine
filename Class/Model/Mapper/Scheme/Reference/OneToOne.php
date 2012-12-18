<?php

/**
 * Тип ссылки "один-к-одному"
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Reference_OneToOne extends 
    Model_Mapper_Scheme_Reference_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data($modelName, $id)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $modelManager = $serviceLocator->getService('modelName');
        $queryBuilder = $serviceLocator->getService('query');
        $query = $queryBuilder
            ->where($this->getModel() . '.' . $this->getField(), $id);
		$item = $modelManager->byQuery($this->getModel(), $query);
		return $this->resource()->setItems(array($item));
	}
}