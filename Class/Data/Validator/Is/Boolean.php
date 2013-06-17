<?php

/**
 * Является ли переменная булевским значением
 * 
 * @author morph
 */
class Data_Validator_Is_Boolean extends Data_Validator_Abstract
{
    /**
     * @inheritdoc
     */
	public function validate($data, $value = true)
	{
        return is_bool($data) == $value;
	}
}