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
	
    public function validateEx ($field, stdClass $data, stdClass $scheme)
    {
		$length = strlen ($data->$field);
		
		if (
			isset ($scheme->$field ['minLength']) &&
			$length < $scheme->$field ['minLength']
		)
		{
			return __CLASS__ . '/' . self::SHORT;
		}
		
		if (
			isset ($scheme->$field ['maxLength']) &&
			$length > $scheme->$field ['maxLength']
		)
		{
			return __CLASS__ . '/' . self::LONG;
		}
    	
		return true;
    }
	
}