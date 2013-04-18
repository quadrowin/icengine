<?php

/**
 * Является ли значение поля идентичным переданому в транспортe
 * 
 * @author morph
 */
class Model_Validator_Attribute_SameAs extends 
    Model_Validator_Attribute_Abstract
{
    /**
     * @inheritdoc
     */
	public function doValidate()
	{
		return $this->model->sfield($this->field) === 
            $this->input->receive($this->value);
	}
}