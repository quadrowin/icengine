<?php

/**
 * Тип ссылки через Helper_Link
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Reference_Link extends 
    Model_Mapper_Scheme_Reference_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data ($modelName, $id)
	{
		$modelManager = $this->getService('modelManager');
		$model = $modelManager->byKey($modelName, $id);
		$helperLink = $this->getService('helperLink');
		if ($model) {
			$collection = $helperLink->linkedItems(
				$model, $this->getModel()
			);
			return $this->resource()->setItems($collection->items());
		}
	}
}