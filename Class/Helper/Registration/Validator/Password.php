<?php

class Helper_Registration_Validator_Password
{
    
    const PASSWORD_EMPTY	= 'passwordEmpty'; // Пустой пароль
    
	const PASSWORD_SHORT	= 'passwordShort'; // Короткий пароль
	
	const PASSWORD_LONG     = 'passwordLong'; // Короткий пароль
	
	public static function validate (stdClass $data)
	{
		if (empty ($data->password))
		{
			return self::PASSWORD_EMPTY;
		}
		
		if (strlen ($data->password) < 6)
		{ 
		    return self::PASSWORD_SHORT;
		}
		
		if (strlen ($data->password) > 250)
		{
		    return self::PASSWORD_LONG;
		}
	    
	    return Registration::OK;
	}
	
}