<?php

/**
 * 
 * @desc Проверка корректности телефонного номера.
 * @author Юрий
 *
 */

class Data_Validator_Registration_Phone
{
	
	const PHONE_SHORT = 'phoneShort';
	
	const PHONE_LONG = 'phoneLong';
	
	public static function validate (stdClass $data, $name, array $info)
	{
		$value = $data->$name = trim ($data->$name);
		$length = strlen ($value);
		
		if ($length < $info ['minLength'])
		{
			return self::PHONE_SHORT;
		}
		
		if ($length > $info ['maxLength'])
		{
			return self::PHONE_LONG;
		}
    	
		return true;
	}
	
}