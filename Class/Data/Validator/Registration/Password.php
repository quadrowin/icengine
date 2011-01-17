<?php

/**
 * 
 * @desc Проверка валидности пароля.
 * @author Юрий
 *
 */

class Data_Validator_Registration_Password
{
    
    const PASSWORD_EMPTY	= 'passwordEmpty'; // Пустой пароль
    
	const PASSWORD_SHORT	= 'passwordShort'; // Короткий пароль
	
	const PASSWORD_LONG     = 'passwordLong'; // Короткий пароль
	
	public static function validate (stdClass $data, $name, array $info)
	{
		if (empty ($data->$name))
		{
			return self::PASSWORD_EMPTY;
		}
		
		if (strlen ($data->$name) < $info ['minLength'])
		{ 
		    return self::PASSWORD_SHORT;
		}
		
		if (strlen ($data->$name) > $info ['maxLength'])
		{
		    return self::PASSWORD_LONG;
		}
	    
	    return true;
	}
	
}