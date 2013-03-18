<?php

/**
 * Проверка на непустоту
 */
class Model_Validator_Attribute_NotEmpty extends 
    Model_Validator_Attribute_Abstract
{
    /**
     * @inheritdoc
     */
	public function doValidate()
	{
        return !empty($this->model->sfield($this->field));
	}
}