<?php

class Data_Validator_Current_User_Password 
{
	
	const OLD_PASSWORD_INCORRECT = 'oldPasswordIncorrect';
	
	public static function validate (stdClass $data, $field, $info)
	{
		if ($data->$field != User::getCurrent ()->password)
		{
			return self::OLD_PASSWORD_INCORRECT;
		}
		
		unset ($data->$field);
		
		return true;
	}
	
}