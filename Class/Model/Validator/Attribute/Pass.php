<?php

/**
 * Валидация через стронний метод
 * 
 * @author morph
 */
class Model_Validator_Attribute_Pass extends Model_Validator_Attribute_Abstract
{
    /**
     * @inheritdoc
     */
	public static function doValidate()
	{
		$result = true;
		foreach ($this->value as $validator) {
			list($className, $methodName) = explode('::', $validator);
			$result &= call_user_func_array(
				array(new $className, $methodName),
				array($this->model, $this->field, $this->value, $this->input)
			);
            if (!$result) {
                break;
            }
		}
		return $result;
	}
}