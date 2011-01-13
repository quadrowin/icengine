<?php

class Helper_Registration_Validator_Password
{
    
    const PASSWORD_EMPTY	= 'passwordEmpty'; // Пустой пароль
    
	const PASSWORD_SHORT	= 'passwordShort'; // Короткий пароль
	
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
	    
	    return Registration::OK;
	}
	
}