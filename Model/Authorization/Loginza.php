<?php

/**
 * Авторизация через логинзу.
 *
 * @author Юрий Шведов
 * @package IcEngine
 */
class Authorization_Loginza extends Authorization_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::authorize()
	 */
	public function authorize($data)
	{
		$authorizationLoginzaToken = $this->getService(
			'authorizationLoginzaToken'
		);
		$token = $authorizationLoginzaToken->tokenData();
		$userLoginza = $this->getService('userLoginza');
		$loginza = $userLoginza->byToken($token, true, true);
		$user = $loginza ? $loginza->User : null;
		if (!$user) {
			$user = $this->autoregister($token);
		}
		return $user instanceof User ? $user->authorize() : $user;
	}

	/**
	 * Авторегистрация
	 *
	 * @param Authorization_Loginza_Token $token
	 * @return User|string
	 */
	public function autoregister(Authorization_Loginza_Token $token)
	{
		if (!$token->email) {
			return "Data_Validator_Loginza_Token::invalid";
		}
		$data = $token->data('data');
		$userService = $this->getService('user');
		$user = $userService->create(array(
			'name'		=> $token->extractName(),
			'login'		=> (string) $token->identity,
			'email'		=> (string) $token->email,
			'password'	=> md5(time()),
			'phone'		=>
				isset($data['phone']) && is_string($data['phone']) ?
					$data['phone'] :
					'',
			'active'	=> 1
		));
		$modelManager = $this->getService('modelManager');
		$query = $this->getService('query');
		$ul = $modelManager->byKey(
			'User_Loginza',
			$query->where('identity', (string) $token->identity)
		);
		if ($ul) {
			$ul->update(array(
				'User__id'	=> $user->key()
			));
		} else {
			$helperDate = $this->getService('helperDate');
			$ul = new User_Loginza(array(
				'User__id'	=> $user->key(),
				'identity'	=> (string) $token->identity,
				'email'		=> (string) $token->email,
				'provider'	=> (string) $token->provider,
				'data'		=> json_encode($data),
				'createdAt'	=> $helperDate->toUnix()
			));
			$ul->save();
		}
		return $user;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isRegistered()
	 */
	public function isRegistered($login)
	{
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isValidLogin()
	 */
	public function isValidLogin($login)
	{
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::findUser()
	 */
	public function findUser($data)
	{
		$authorizationLoginzaToken = $this->getService(
			'authorizationLoginzaToken'
		);
		$token = $authorizationLoginzaToken->tokenData();
		$userLoginza = $this->getService('userLoginza');
		$loginza = $userLoginza->byToken($token);
		return $loginza ? $loginza->User : null;
	}
}