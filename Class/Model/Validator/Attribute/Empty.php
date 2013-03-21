<?php

/**
 * Проверка на пустоту
 */
class Model_Validator_Attribute_Empty extends 
    Model_Validator_Attribute_Abstract
{
    /**
     * @inheritdoc
     */
	public function doValidate()
	{
        return (bool) $this->model->sfield($this->field);
	}
}