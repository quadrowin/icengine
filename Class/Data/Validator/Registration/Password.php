<?php

/**
 * 
 * @desc Проверка валидности пароля.
 * @author Юрий
 *
 */

class Data_Validator_Registration_Password
{
    
	const SHORT	= 'short';	// Короткий пароль
	
	const LONG	= 'long';	// Короткий пароль
	
	public function validateEx ($field, $data, stdClass $scheme)
	{
		$length = strlen ($data->$field);
		$param = $scheme->$field;
		
		$min = isset ($param ['minLength']) ? $param ['minLength'] : 6;
		$max = isset ($param ['maxLength']) ? $param ['maxLength'] : 50;
		
		if ($length < $min)
		{ 
		    return __CLASS__ . '/' . self::SHORT;
		}
		
		if ($length > $max)
		{
		    return __CLASS__ . '/' . self::LONG;
		}
	    
	    return true;
	}
	
}