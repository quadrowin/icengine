<?php

/**
 * Является ли переменная объектом
 * 
 * @author morph
 */
class Data_Validator_Is_Object extends Data_Validator_Abstract 
{
    /**
     * @inheritdoc
     */
	public function validate($data, $value = true)
	{
        return (bool) (is_object($data) == $value);
    }
}