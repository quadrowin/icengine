<?php

class Data_Validator_Registration_Email
{

	const SHORT		= 'short';      // Пустой емейл
	
	const INCORRECT	= 'incorrect';  // Емейл некорректен
	
	const REPEAT	= 'repeat';     // Уже используется
    
    public static function validate (stdClass $data, $name, array $info)
    {
		if (empty ($data->$name))
		{
			return __CLASS__ . '/' . self::SHORT;
		}
		
		$email = $data->$name = trim ($data->$name);
		
		if (
		    !filter_var ($email, FILTER_VALIDATE_EMAIL) ||
		    strlen ($email) > $info ['maxLength']
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