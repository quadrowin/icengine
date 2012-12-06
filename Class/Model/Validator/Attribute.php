<?php

/**
 * Атрибуты для валидация модели
 * 
 * @author morph
 * @Service("modelValidatorAttribute")
 */
class Model_Validator_Attribute
{
    /**
     * Валидировать
     * 
     * @param string $name
     * @param Model $model
     * @param string $field
     * @param mixed $value
     * @param array $input
     * @return boolean
     */
	public function validate ($name, $model, $field, $value, $input)
	{
		$model_name = 'Model_Validator_Attribute_' . ucfirst ($name);
		return $model_name::validate ($model, $field, $value, $input);
	}
}