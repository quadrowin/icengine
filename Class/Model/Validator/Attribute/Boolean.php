<?php

/**
 * Является ли поле модели булевским значением
 * 
 * @author morph
 */
class Model_Validator_Attribute_Boolean extends 
    Model_Validator_Attribute_Abstract
{
    /**
     * @inheritdoc
     */
	public function doValidate()
	{
        if (is_null($this->value)) {
            $this->value = true;
        }
		return is_bool($this->model->sfield($this->field)) === 
            (bool) $this->value;
	}
}