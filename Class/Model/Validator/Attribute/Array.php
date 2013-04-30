<?php

/**
 * Являет ли поле массивом
 * 
 * @author morph
 */
class Model_Validator_Attribute_Array extends Model_Validator_Attribute_Abstract
{
    /**
     * @inheritdoc
     */
	public function doValidate()
	{
        if (is_null($this->value)) {
            $this->value = true;
        }
		return is_array($this->model->sfield($this->field)) === 
            (bool) $this->value;
	}
}