<?php

class Data_Validator_Email extends Data_Validator_Abstract
{
	
	public function validate ($data)
	{
		return filter_var ($data, FILTER_VALIDATE_EMAIL);
	}
	
}