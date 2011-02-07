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
	
	public function validateEx ($field, $data, stdClass $scheme)
	{
		$length = strlen ($data->$field);
		$param = $scheme->$field;
		
		if ($length < $param ['minLength'])
		{ 
		    return __CLASS__ . '/' . self::SHORT;
		}
		
		if ($length > $param ['maxLength'])
		{
		    return __CLASS__ . '/' . self::LONG;
		}
	    
	    return true;
	}
	
}