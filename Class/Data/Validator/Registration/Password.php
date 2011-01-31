<?php

/**
 * 
 * @desc Проверка валидности пароля.
 * @author Юрий
 *
 */

class Data_Validator_Registration_Password
{
    
	const SHORT	= 'short'; // Короткий пароль
	
	const LONG	= 'long'; // Короткий пароль
	
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