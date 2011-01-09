<?php

class Helper_Registration_Validator_Email
{

	const EMAIL_EMPTY		= 'emailEmpty';      // Пустой емейл
	
	const EMAIL_INCORRECT	= 'emailIncorrect';  // Емейл некорректен
	
	const EMAIL_REPEAT		= 'emailRepeat';     // Уже используется
    
    public function validate (array &$data, $name)
    {
		if (empty ($data ['email']))
		{
			return self::EMAIL_EMPTY;
		}
		
		$data ['email'] = trim ($data ['email']);
		$email = $data ['email'];
		
		if (
		    !filter_var ($email, FILTER_VALIDATE_EMAIL) ||
		    strlen ($email) > 40
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