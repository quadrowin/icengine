<?php
/**
 * 
 * @desc Авторизация по логину и паролю
 * @author Юрий Шведов
 * @package IcEngine
 *
 */

if (!class_exists ('User_Authorization_Abstract'))
{
	require dirname (__FILE__) . '/../Abstract.php';
}

class User_Authorization_Login_Password extends User_Authorization_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see User_Authorization_Abstract::findUser()
	 */
	public function findUser ($data)
	{
		return IcEngine::$modelManager->modelBy (
			'User',
			Query::instance ()
			->where ('email', $data ['email'])
			->where ('password', $data ['password'])
		);
	}
	
}