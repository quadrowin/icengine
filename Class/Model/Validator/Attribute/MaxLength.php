<?php

/**
 * Проверка на максимальную длину
 * 
 * @author morph
 */
class Model_Validator_Attribute_MaxLength extends 
    Model_Validator_Attribute_Abstract
{
    /**
     * @inheritdoc
     */
	public function doValidate()
	{
		$field = $this->model->sfield($this->field);
		return is_string($field) && strlen($field) <= $this->value;
	}
}