<?php

/**
 * Внешняя связь по полю, созданному из значений 2 полей
 * 
 * @author neon
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Reference_ExternalByGlueFields extends 
    Model_Mapper_Scheme_Reference_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Reference_Abstract::data
	 */
	public function data($modelName, $id)
	{
        list($_id, $field1, $glue, $field2) = $this->_field;
		$modelManager = $this->getService('modelManager');
		$model = $modelManager->byKey($modelName, $id);
        $result_field = $model->$field1 . $glue . $model->$field2;
		$query = $this->getService('query');
		$item = $modelManager->byQuery(
			$this->getModel(),
			$query->where(
				$this->getModel() . '.' . $_id,
				$result_field
			)
		);
		return $this->resource()->setItems(array($item));
	}
}