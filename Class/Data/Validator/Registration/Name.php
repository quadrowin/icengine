<?php

/**
 * 
 * @desc	Проверка имени.
 * @author	Юрий
 *
 */

class Data_Validator_Registration_Name
{
    
	const NAME_SHORT	= 'nameShort';
	
	const NAME_LONG		= 'nameLong';
	
    public static function validate (stdClass $data, $name, array $info)
    {
		$value = $data->$name = trim ($data->$name);
		$length = strlen ($value);
		
		if ($length < $info ['minLength'])
		{
			return self::NAME_SHORT;
		}
		
		if ($length > $info ['maxLength'])
		{
			return self::NAME_LONG;
		}
    	
		return true;
    }
    
}