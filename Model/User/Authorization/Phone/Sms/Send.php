<?php
/**
 * 
 * @desc Аавторизация через отправку пользователю СМС сообщения с кодом.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */

if (!class_exists ('User_Authorization_Abstract'))
{
	require dirname (__FILE__) . '/../../Abstract.php';
}

class User_Authorization_Phone_Sms_Send extends User_Authorization_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see User_Authorization_Abstract::findUser()
	 */
	public function findUser ($data)
	{
		Loader::load ('Helper_Activation');
		$activation = Helper_Activation::byShortCode (
			$data ['sms_session_id'],
			$data ['sms_session_code']
		);
	}
	
}