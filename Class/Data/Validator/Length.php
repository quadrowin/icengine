<?php

class Data_Validator_Length extends Data_Validator_Abstract
{
	public function validate ($data, $needle)
	{
		$length = -1;
		if (is_array ($data))
		{
			$length = sizeof ($data);
		}
		elseif (is_string ($data))
		{
			$length = strlen ($data);
		}
		return (bool) ($length <= $needle);
	}
}