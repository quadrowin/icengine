<?php

/**
 * Является ли поле модели объектом
 * 
 * @author morph
 */
class Model_Validator_Attribute_Object extends Model_Validator_Attribute_Abstract
{
    /**
     * @inheritdoc
     */
	public function doValidate()
	{
        if (is_null($this->value)) {
            $this->value = true;
        }
		return is_object($this->model->sfield($this->field)) === 
            (bool) $this->value;
	}
}