<?php

/**
 * 
 * Абстрактный класс фильтра
 * @author Юрий
 *
 */
abstract class Filter_Abstract
{
	
	/**
	 * Обычная фильтрация
	 * @param string $data
	 * @return string
	 */
	public function filter ($data)
	{
		return $data;
	}
	
	/**
	 * Фильтрация с использование схемы
	 * @param string $field
	 * 		Имя поля.
	 * @param stdClass $data
	 * 		Все данные.
	 * @param stdClass $scheme
	 * 		Схема.
	 */
	public function filterEx ($field, stdClass $data, 
		stdClass $scheme)
	{
		return $this->filter ($data->$field);
	}
	
}