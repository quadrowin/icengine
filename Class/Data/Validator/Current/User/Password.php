<?php

class Data_Validator_Current_User_Password 
{
	
	const INCORRECT = 'incorrect';
	
	public static function validate (stdClass $data, $field, $info)
	{
		if ($data->$field != User::getCurrent ()->password)
		{
			return __CLASS__ . '/' . self::INCORRECT;
		}
		
		unset ($data->$field);
		
		return true;
	}
	
}