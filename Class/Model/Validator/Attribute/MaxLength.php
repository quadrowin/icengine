<?php

Loader::load ('Model_Validator_Attribute_Abstract');

class Model_Validator_Attribute_MaxLength extends Model_Validator_Attribute_Abstract
{
	public static function validate ($model, $field, $value, $input)
	{
		$field = $model->sfield ($field);
		return is_string ($field) && strlen ($field) <= $value;
	}
}