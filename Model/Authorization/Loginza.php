<?php
/**
 * 
 * @desc Авторизация через логинзу.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
Loader::load ('Authorization_Abstract');
class Authorization_Loginza extends Authorization_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::authorize()
	 */
	public function authorize ($data)
	{
		Loader::load ('Authorization_Loginza_Token');
		$token = Authorization_Loginza_Token::tokenData ();
		
		if (!$token->email)
		{
			return "Data_Validator_Loginza_Token::invalid";
		}
		
		$user = IcEngine::$modelManager->modelBy (
			'User',	
			Query::instance ()
			->where ('email', $token->email)
		);
		
		if (!$user)
		{
			$user = $this->autoregister ($token);
		}
		
		return $user instanceof User ? $user->authorize () : $user;
	}
	
	public function autoregister (Authorization_Loginza_Token $token)
	{
		if (!$token->email)
		{
			return "Data_Validator_Loginza_Token::invalid";
		}
		
		$data = $token->data ('data');
		
		Loader::load ('Helper_Email');
		$user = User::create (array (
			'name'		=> Helper_Email::extractName ($token->email),
			'email'		=> $token->email,
			'password'	=> md5 (time ()),
			'phone'		=> 
				(isset ($data ['phone']) && is_string ($data ['phone'])) ? 
					$data ['phone'] : 
					'',
			'active'	=> 1
		));
		return $user;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isRegistered()
	 */
	public function isRegistered ($login)
	{
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isValidLogin()
	 */
	public function isValidLogin ($login)
	{
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::findUser()
	 */
	public function findUser ($data)
	{
		return null;
	}
	
}