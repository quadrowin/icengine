<?php

/**
 * 
 * Стандартная проверка строки
 * @author Юрий
 *
 */

class Data_Validator_Standart_String
{
	
	/**
	 * Слишком короткая строка
	 * @var string
	 */
	const SHORT	= 'short';
	
	/**
	 * Слишком длинная строка
	 * @var string
	 */
	const LONG	= 'long';
	
    public function validateEx ($field, $data, stdClass $scheme)
    {
		$length = strlen ($data->$field);
		$param = $scheme->$field;
		
		if (isset ($param ['minLength']) && $length < $param ['minLength'])
		{
			return __CLASS__ . '/' . self::SHORT;
		}
		
		if (isset ($param ['maxLength']) && $length > $param ['maxLength'])
		{
			return __CLASS__ . '/' . self::LONG;
		}
    	
		return true;
    }
	
}