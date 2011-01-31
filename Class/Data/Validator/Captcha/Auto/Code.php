<?php

class Data_Validator_Captcha_Auto_Code
{
	
	const FAIL = 'fail';
	
	public static function validate ($data)
	{
		Loader::load ('Helper_Captcha');
		
		if (
			!isset ($data, $_SESSION [Helper_Captcha::SF_AUTO_CODE]) ||
			$_SESSION [Helper_Captcha::SF_AUTO_CODE] != $data
		)
		{
			return __CLASS__ . '/' . self::FAIL;
		}
		
		unset ($_SESSION [Helper_Captcha::SF_AUTO_CODE]);
		
		return true;
	}
	
}