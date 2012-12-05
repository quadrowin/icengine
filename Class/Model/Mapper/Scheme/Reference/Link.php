<?php

/**
 * Тип ссылки через Helper_Link
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Reference_Link extends Model_Mapper_Scheme_Reference_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data ($model_name, $id)
	{
		$modelManager = $this->getService('modelManager');
		$model = $modelManager->byKey($model_name, $id);
		$helperLink = $this->getService('helperLink');
		if ($model) {
			$collection = $helperLink->linkedItems(
				$model, $this->getModel()
			);
			return $this->resource()
				->setItems($collection->items());
		}
	}
}