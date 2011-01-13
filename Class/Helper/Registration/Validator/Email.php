<?php

class Helper_Registration_Validator_Email
{

	const EMAIL_EMPTY		= 'emailEmpty';      // Пустой емейл
	
	const EMAIL_INCORRECT	= 'emailIncorrect';  // Емейл некорректен
	
	const EMAIL_REPEAT		= 'emailRepeat';     // Уже используется
    
    public static function validate (stdClass $data, $name, array $info)
    {
		if (empty ($data->email))
		{
			return self::EMAIL_EMPTY;
		}
		
		$email = $data->email = trim ($data->email);
		
		if (
		    !filter_var ($email, FILTER_VALIDATE_EMAIL) ||
		    strlen ($email) > $info ['maxLength']
		)
		{
		    return self::EMAIL_INCORRECT;
		}
		
		$user = IcEngine::$modelManager->modelBy (
		    'User',
		    Query::instance ()
		    ->where ('email', $email)
		);
		
		if ($user)
		{
			return self::EMAIL_REPEAT;
		}
		
		$reg = IcEngine::$modelManager->modelBy (
		    'Registration',
		    Query::instance ()
		    ->where ('email', $email)
		);
		
		if ($reg)
		{
			return self::EMAIL_REPEAT;
		}
		
		return Registration::OK;
    }
    
}