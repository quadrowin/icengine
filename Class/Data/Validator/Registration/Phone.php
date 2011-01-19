<?php

/**
 * 
 * @desc Проверка корректности телефонного номера.
 * @author Юрий
 *
 */

class Data_Validator_Registration_Phone
{
	
	const SHORT	= 'short';
	
	const LONG	= 'long';
	
	public static function validate (stdClass $data, $name, array $info)
	{
		$value = $data->$name = trim ($data->$name);
		$length = strlen ($value);
		
		if ($length < $info ['minLength'])
		{
			return __CLASS__ . '/' . self::SHORT;
		}
		
		if ($length > $info ['maxLength'])
		{
			return __CLASS__ . '/' . self::LONG;
		}
    	
		return true;
	}
	
}