<?php
/**
 * 
 * @desc Авторизация через смс.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */

if (!class_exists ('User_Authorization_Abstract'))
{
	require dirname (__FILE__) . '../Abstract.php';
}

class User_Authorization_Sms extends User_Authorization_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see User_Authorization_Abstract::findUser()
	 */
	public function findUser ($data)
	{
		$sms_session = IcEngine::$modelManager->modelBy (
			'Sms_Session',
			Query::instance ()
			->where ('id', $data ['sms_session_id'])
			->where ('code', $data ['sms_session_code'])
		);
		return $sms_session ? $sms_session->User : null; 
	}
	
}