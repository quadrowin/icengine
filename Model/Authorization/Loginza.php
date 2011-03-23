<?php
/**
 * 
 * @desc Авторизация через логинзу
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Authorization_Loginza extends Authorization_Abstract
{
	
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