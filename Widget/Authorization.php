<?php

class Widget_Authorization extends Widget_Abstract
{
    
    const DEFAULT_REDIRECT = '/';
    
    public function authDialog ()
    {
        
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
		    $this->_template = 'Widget/Authorization/login/password_incorrect.tpl';
		    return ;
		}
    }
    
}