<?php

/**
 * Тип ссылки через Model_Component
 *
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Reference_Component extends 
    Model_Mapper_Scheme_Reference_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data($modelName, $id)
	{
		$model = $this->getService('modelManager')->byKey($modelName, $id);
		if ($model) {
			$collection = $model->component(
				substr ($this->getModel(), strlen('Component_'))
			);
			return $this->resource()
				->setItems($collection->items());
		}
	}
}