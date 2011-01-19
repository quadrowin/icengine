<?php

class Data_Validator_Captcha_Auto_Code
{
	
	const FAIL = 'fail';
	
	public static function validate (stdClass $data, $name, array $info)
	{
		Loader::load ('Helper_Captcha');
		
		if (
			!isset ($data->$name, $_SESSION [Helper_Captcha::SF_AUTO_CODE]) ||
			$_SESSION [Helper_Captcha::SF_AUTO_CODE] != $data->$name
		)
		{
			return __CLASS__ . '/' . self::FAIL;
		}
		
		unset ($_SESSION [Helper_Captcha::SF_AUTO_CODE]);
		unset ($data->$name);
		
		return true;
	}
	
}