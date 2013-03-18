<?php

/**
 * Абстрактный класс валидатора
 * 
 * @author goorus, morph
 */
abstract class Data_Validator_Abstract 
{
	/**
     * Ошибка валидации
     */
	const INVALID = 'invalid';
	
	/**
	 * Валидация строки
	 * 
     * @param string $data Данные.
	 * @return true|string
	 * 		true, если данные прошли валидацию или 
	 * 		строка ошибки.
	 */
	public function validate($data)
	{
		return true;
	}
	
	/**
	 * Валидация поля с использованием схемы
	 * 
     * @param string $field
	 * 		Название поля.
	 * @param stdClass $data
	 * 		Все данные.
	 * @param stdClass|Objective $scheme
	 * 		Схема.
	 * @return true|string
	 * 		true, если данные прошли валидацию или
	 * 		строка ошибки.
	 */
	public function validateEx($field, $data, $scheme)
	{
		return $this->validate($data->$field) === true 
            ? true : get_class($this) . '/' . self::INVALID;
	}
}