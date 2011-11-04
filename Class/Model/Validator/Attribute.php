<?php

class Model_Validator_Attribute 
{
	public static function validate ($name, $model, $field, $value, $input)
	{
		$model_name = 'Model_Validator_Attribute_' . ucfirst ($name);
		Loader::load ($model_name);
		return $model_name::validate ($model, $field, $input);
	}
}