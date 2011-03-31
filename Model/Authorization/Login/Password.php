<?php
/**
 * 
 * @desc Авторизация по логину и паролю.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
Loader::load ('Authorization_Abstract');
class Authorization_Login_Password extends Authorization_Abstract
{
	
	/**
	 * @desc Configuration
	 * @var array
	 */
	protected $_config = array (
		'check_regexp'	=> '^[a-zA-Z][a-zA-Z0-9]{1,19}$'
	);
	
	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::authorize()
	 */
	public function authorize ($data)
	{
		
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isRegistered()
	 */
	public function isRegistered ($login)
	{
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isValidLogin()
	 */
	public function isValidLogin ($login)
	{
		return (bool) preg_match ($this->config ()->check_regexp, $login);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::findUser()
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