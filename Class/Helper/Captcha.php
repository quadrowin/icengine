<?php
/**
 * 
 * @desc Помощник для каптчи.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Helper_Captcha
{
	
	const SF_AUTO_CODE = 'Captcha_Auto_Code';
	
	/**
	 * 
	 * @return string
	 */
	public static function generateAutocode ()
	{ 
		return substr (md5 (time () . __METHOD__), 3, 10);
	}
	
	/**
	 * 
	 * @return array
	 */
	public static function generatePair ()
	{
		$code = time ();
		return array ($code, md5 ($code));
	}
	
	public static function check ($code)
	{
		
	}
	
}