<?php

/**
 * Абстрактный класс валидатора
 * 
 * @author goorus, morph
 */
abstract class Data_Validator_Abstract 
{	
	/**
	 * Валидация строки
	 * 
     * @param string $data Данные.
	 * @return true|string
	 * 		true, если данные прошли валидацию или 
	 * 		строка ошибки.
	 */
	public function validate($data, $value = null)
	{
		return true;
	}
}