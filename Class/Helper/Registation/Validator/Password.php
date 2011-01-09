<?php

class Helper_Registration_Validator_Password
{
    
    const PASSWORD_EMPTY	= 'passwordEmpty'; // Пустой пароль
    
	const PASSWORD_SHORT	= 'passwordShort'; // Короткий пароль
	
	public static function validate (array $data)
	{
	    $password = isset ($data ['password']) ? $data ['password'] : null;
		
		if (empty ($password))
		{
			return self::PASSWORD_EMPTY;
		}
		
		if (strlen ($password) < 6)
		{ 
		    return self::PASSWORD_SHORT;
		}
	    
	    return Registration::OK;
	}
	
}