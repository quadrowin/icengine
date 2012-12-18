<?php

/**
 * Тип ссылки через Attribute_Manager
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Reference_Attribute extends 
    Model_Mapper_Scheme_Reference_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data($modelName, $id)
	{
		$modelManager = $this->getService('modelManager');
		$model = $modelManager->byKey($modelName, $id);
		return $this->resource()->setItems(array(
			$model->attr($this->getField())
		));
	}
}