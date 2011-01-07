<?php

class Controller_Authorization extends Controller_Abstract
{
    
    const DEFAULT_REDIRECT = '/';
	
	function accessDenied ()
	{
		$this->_output->send ('user', User::getCurrent ());
	}
	
	public function login ()
	{
		$login = $this->_input->receive ('login');
		$password = $this->_input->receive ('password');
		$redirect = $this->_input->receive ('redirect');
		
		$redirect = $redirect ? $redirect : self::DEFAULT_REDIRECT;

		Loader::load ('Authorization');
		$user = Authorization::authorize ($login, $password);
		
		Loader::load ('Common_Uri');
		if ($user)
		{
			$redirect = Common_Uri::replaceGets (
			    array ('autherror' => null), 
			    false, $redirect);
		}
		else
		{
			$redirect = Common_Uri::replaceGets (
			    array ('autherror' => 1),    
			    false, $redirect);
		}
		
		Loader::load ('Header');
		Header::redirect ($redirect);
		die ();
	}
	
	public function logout ()
	{
	    User_Session::getCurrent ()->delete ();
	    $redirect = $this->_input->receive ('redirect');
	    
	    Header::redirect ($redirect ? $redirect : self::DEFAULT_REDIRECT);
	    die ();
	}
	
}