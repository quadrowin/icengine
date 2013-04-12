<?php

/**
 * Является ли поле модели экземпяром класса
 * 
 * @author morph
 */
class Model_Validator_Attribute_InstanceOf extends 
    Model_Validator_Attribute_Abstract
{
    /**
     * @inheritdoc
     */
	public function doValidate()
	{
        return ($this->model->sfield($this->field) instanceof $this->value);
	}
}