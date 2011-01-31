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
	
	public static function validateEx ($field, stdClass $data, stdClass $scheme)
	{
		$length = strlen ($data->$field);
		
		if ($length < $scheme->$field ['minLength'])
		{
			return __CLASS__ . '/' . self::SHORT;
		}
		
		if ($length > $scheme->$field ['maxLength'])
		{
			return __CLASS__ . '/' . self::LONG;
		}
    	
		return true;
	}
	
}