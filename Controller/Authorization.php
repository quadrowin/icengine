<?php

class Controller_Authorization extends Controller_Abstract
{
    
    const DEFAULT_REDIRECT = '/';
	
	function accessDenied ()
	{
		$this->_output->send ('user', User::getCurrent ());
	}
	
	public function authDialog ()
	{
	
	}
	
	public function login ()
	{
		$login = $this->_input->receive ('login');
		$password = $this->_input->receive ('password');
		$redirect = $this->_input->receive ('redirect');
		
		Loader::load ('Helper_Uri');
		$redirect = Helper_Uri::validRedirect (
			$redirect ? $redirect : self::DEFAULT_REDIRECT
		);

		Loader::load ('Authorization');
		
		$user = Authorization::authorize ($login, $password);
		
		if ($user)
		{
			$this->_output->send ('data', array (
				'user'	=> array (
		            'id'	=> $user->id,
		            'name'	=> $user->name
		        ),
		        'redirect'	=> $redirect
		    ));
		}
		else
		{
		    $this->_output->send ('data', array (
		        'error'	=> 'Password incorrect'
		    ));
		    $this->_dispatcherIteration->setTemplate (
		    	str_replace (array ('::', '_'), '/', __METHOD__) .
		    	'/password_incorrect.tpl'
		    );
		}
	}
	
	public function logout ()
	{
	    User_Session::getCurrent ()->delete ();
	    $redirect = $this->_input->receive ('redirect');
	    
	    Loader::load ('Helper_Uri');
	    $redirect = Helper_Uri::validRedirect (
	    	$redirect ? $redirect : self::DEFAULT_REDIRECT
	    );
	    
	    $this->_output->send ('data', array (
	    	'redirect'	=> $redirect
		));
	}
	
}