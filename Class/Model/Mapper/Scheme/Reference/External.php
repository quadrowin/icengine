<?php

/**
 * Внешняя связь
 *
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Reference_External 
extends Model_Mapper_Scheme_Reference_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data($model_name, $id)
	{
		$modelManager = $this->getService('modelManager');
		$modelScheme = $this->getService('modelScheme');
		$model = $modelManager->byKey($model_name, $id);
		$kf = $modelScheme->keyField($this->getModel());
		$query = $this->getService('query');
		$item = $modelManager->byQuery(
			$this->getModel(),
			$query->where($this->getModel() . '.' . $kf,
					$model->sfield($this->getField()))
		);
		return $this->resource()
			->setItems(array($item));
	}
}