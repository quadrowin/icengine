<?php

/**
 * Внешняя связь
 *
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Reference_External extends 
    Model_Mapper_Scheme_Reference_Abstract
{
	/**
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data($modelName, $id)
	{
		$modelManager = $this->getService('modelManager');
		$modelScheme = $this->getService('modelScheme');
		$model = $modelManager->byKey($modelName, $id);
		$kf = $modelScheme->keyField($this->getModel());
		$query = $this->getService('query');
		$item = $modelManager->byQuery(
			$this->getModel(),
			$query->where($this->getModel() . '.' . $kf, 
                $model->sfield($this->getField())
            )
		);
		return $this->resource()->setItems(array($item));
	}
}