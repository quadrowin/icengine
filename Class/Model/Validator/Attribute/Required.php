<?php

/**
 * Проверка значения поля на null
 * 
 * @author morph
 */
class Model_Validator_Attribute_Required extends 
    Model_Validator_Attribute_Abstract
{
    /**
     * @inheritdoc
     */
	public function doValidate()
	{
		return !is_null($this->model->sfield($this->field));
	}
}