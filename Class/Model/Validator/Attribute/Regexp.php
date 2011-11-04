<?php

Loader::load ('Model_Validator_Attribute_Abstract');

class Model_Validator_Attribute_NotEmpty extends Model_Validator_Attribute_Abstract
{
	public static function validate ($model, $field, $value, $input)
	{
		return preg_math ('#' . $value . '#', $model->sfield ($value));
	}
}