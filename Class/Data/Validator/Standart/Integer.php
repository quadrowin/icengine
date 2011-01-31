<?php

/**
 * 
 * Стандартная проверка числа.
 * @author Юрий
 *
 */

class Data_Validator_Standart_Integer
{
	
	/**
	 * Слишком маленькое число
	 * @var string
	 */
	const SMALL	= 'small';
	
	/**
	 * Слишком большое число
	 * @var string
	 */
	const BIG	= 'big';
	
    public function validateEx ($field, stdClass $data, stdClass $scheme)
    {
		if (
			isset ($scheme->$field ['min']) &&
			$data->$field < $scheme->$field ['min']
		)
		{
			return __CLASS__ . '/' . self::SMALL;
		}
		
		if (
			isset ($scheme->$field ['max']) && 
			$data->$field > $scheme->$field ['max']
		)
		{
			return __CLASS__ . '/' . self::BIG;
		}
    	
		return true;
    }
	
}