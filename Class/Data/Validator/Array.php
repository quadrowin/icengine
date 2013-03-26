<?php

/**
 * Проверка на то является ли входящий аргумент массивом
 * 
 * @author goorus, morph
 */
class Data_Validator_Array extends Data_Validator_Abstract
{
	/**
     * @inheritdoc
     */
	public function validate($data)
	{
		return (bool) is_array($data);
	}
	
}