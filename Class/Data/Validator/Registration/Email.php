<?php

class Data_Validator_Registration_Email
{

	const SHORT		= 'short';		// Пустой емейл
	
	const INCORRECT	= 'incorrect';	// Емейл некорректен
	
	const REPEAT	= 'repeat';		// Уже используется
	
	public function validateEx ($field, $data, $scheme)
	{
		if (empty ($data->$field))
		{
			return __CLASS__ . '/' . self::SHORT;
		}
		
		$email = $data->$field;
		$param = $scheme->$field;
		
		if (
			!filter_var ($email, FILTER_VALIDATE_EMAIL) ||
			strlen ($email) > $param ['maxLength']
		)
		{
			return __CLASS__ . '/' . self::INCORRECT;
		}
		
		$user = Model_Manager::byKey (
			'User',
			Query::instance ()
				->where ('email', $email)
		);
		
		if ($user)
		{
			return __CLASS__ . '/' . self::REPEAT;
		}
		
		$reg = Model_Manager::byKey (
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