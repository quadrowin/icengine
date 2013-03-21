<?php

/**
 * Являет ли значение поля строкой
 * 
 * @author morph
 */
class Model_Validator_Attribute_String extends 
    Model_Validator_Attribute_Abstract
{
	public function doValidate()
	{
        if (!is_null($this->value)) {
            $this->value = true;
        }
		return is_string($this->model->sfield($this->field)) === 
            (bool) $this->value;
	}
}