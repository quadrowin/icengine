<?php

namespace Ice;

/**
 *
 * @desc Валидатор положительных чисел.
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Data_Validator_Numeric_Positive extends Data_Validator_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Data_Validator_Abstract::validate()
	 */
	public function validate ($data)
	{
		return $data > 0;
	}

}