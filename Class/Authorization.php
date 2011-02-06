<?php

class Authorization
{
	
	public static $config = array (
		'login_field'		=> 'email',
		'password_field'	=> 'password'
	);
	
	/**
	 * 
	 * @param string $login
	 * @param string $password
	 * @return User|null
	 */
	public static function findUser ($login, $password)
	{
		return IcEngine::$modelManager->modelBy (
			'User',
			Query::instance ()
			->where (self::$config ['login_field'], $login)
			->where (self::$config ['password_field'], $password)
			->where ('active=1')
			->order (self::$config ['login_field'])
			->limit (1, 0)
		);
	}
	
	/**
	 * 
	 * @param string $login
	 * @param string $password
	 * @return User|null
	 */
	public static function authorize ($login, $password)
	{
		$user = self::findUser ($login, $password);
		
		if ($user)
		{
			$user->authorize ();
		}
		
		return $user;
	}
	
	public static function logout ($redirect = '/')
	{
		User_Session::getCurrent ()->delete ();
		
		Loader::load ('Header');
		Header::redirect ($redirect);
	}
	
}