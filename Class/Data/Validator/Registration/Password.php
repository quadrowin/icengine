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
	
	public static function validate (stdClass $data, $name, array $info)
	{
		if (strlen ($data->$name) < $info ['minLength'])
		{ 
		    return __CLASS__ . '/' . self::SHORT;
		}
		
		if (strlen ($data->$name) > $info ['maxLength'])
		{
		    return __CLASS__ . '/' . self::LONG;
		}
	    
	    return true;
	}
	
}