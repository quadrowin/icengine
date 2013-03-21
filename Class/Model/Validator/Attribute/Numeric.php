<?php

/**
 * Являет ли значение поля числом
 * 
 * @author morph
 */
class Model_Validator_Attribute_Numeric extends 
    Model_Validator_Attribute_Abstract
{
    /**
     * @inheritdoc
     */
	public function doValidate()
	{
        if (!is_null($this->value)) {
            $this->value = true;
        }
		return is_numeric($this->model->sfield($this->field)) === 
            (bool) $this->value;
	}
}