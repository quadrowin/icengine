<?php

class Data_Validator_Captcha_Auto_Code
{
	
	const FAIL_CAPTCHA_CODE = 'failCaptchaCode';
	
	public static function validate (stdClass $data, $name, array $info)
	{
		Loader::load ('Helper_Captcha');
		
		if (
			!isset ($data->$name, $_SESSION [Helper_Captcha::SF_AUTO_CODE]) ||
			$_SESSION [Helper_Captcha::SF_AUTO_CODE] != $data->$name
		)
		{
			return self::FAIL_CAPTCHA_CODE;
		}
		
		unset ($_SESSION [Helper_Captcha::SF_AUTO_CODE]);
		unset ($data->$name);
		
		return true;
	}
	
}