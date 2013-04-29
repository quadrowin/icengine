<?php

/**
 * Валидатор e-mail адресов
 * 
 * @author goorus, morph
 */
class Data_Validator_Email extends Data_Validator_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function validate ($data)
	{
		return $data && $data == filter_var($data, FILTER_VALIDATE_EMAIL);
	}
}