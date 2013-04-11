<?php

/**
 * Проверка на число
 * 
 * @author goorus, morph
 */
class Data_Validator_Numeric extends Data_Validator_Abstract
{
    /**
     * @inheritdoc
     */
	public function validate($data)
	{
		return (bool) is_numeric($data);
	}
}