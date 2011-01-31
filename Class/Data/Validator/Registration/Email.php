<?php

class Data_Validator_Registration_Email
{

	const SHORT		= 'short';      // Пустой емейл
	
	const INCORRECT	= 'incorrect';  // Емейл некорректен
	
	const REPEAT	= 'repeat';     // Уже используется
    
    public static function validateEx ($field, stdClass $data, stdClass $scheme)
    {
		if (empty ($data->$field))
		{
			return __CLASS__ . '/' . self::SHORT;
		}
		
		$email = $data->$field;
		
		if (
		    !filter_var ($email, FILTER_VALIDATE_EMAIL) ||
		    strlen ($email) > $scheme->$field ['maxLength']
		)
		{
		    return __CLASS__ . '/' . self::INCORRECT;
		}
		
		$user = IcEngine::$modelManager->modelBy (
		    'User',
		    Query::instance ()
		    ->where ('email', $email)
		);
		
		if ($user)
		{
			return __CLASS__ . '/' . self::REPEAT;
		}
		
		$reg = IcEngine::$modelManager->modelBy (
		    'Registration',
		    Query::instance ()
		    ->where ('email', $email)
		);
		
		if ($reg)
		{
			return __CLASS__ . '/' . self::REPEAT;
		}
		
		return true;
    }
    
}