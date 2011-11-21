<?php

class Model_Validator_Abstract
{
	protected static $_scheme = array (

	);

	public static function validate ($model, $input)
	{
		Loader::load ('Data_Validator');
		return Data_Validator::validate ($model, self::$_scheme, $input);
	}
}