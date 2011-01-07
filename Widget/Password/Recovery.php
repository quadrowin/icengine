<?php

class Widget_Password_Recovery extends Widget_Abstract
{
    
    public function change ()
    {
        $password = $this->_input->receive ('password');
    	if (strlen ($password) < 3)
		{
			$this->_output->send ('data', array (
				'error'	=> true
			));
			$this->_template = 'Password/Recovery/change/error_short_password.tpl';
			return null;
		}
        
        Loader::load ('Password_Recovery');
		$recovery = Password_Recovery::fromSession ();
		
		if (!$recovery)
		{
		    $this->_output->send ('data', array (
			    'error'	=> true
		    ));
		    $this->_template = 'Password/Recovery/change/error_recovery_not_found.tpl';
		    return null;
		}
		
		// меняем пароль и делаем неактивной смену
		$recovery->update (array (
			'active'	=> 0
		));
		
		$recovery->User->update (array (
			'password'	=> $password
		));
		
		Password_Recovery::resetSession ();
    }
    
    public function sendCode ()
    {
        Loader::load ('Password_Recovery');
        
    	$email = $this->_input->receive ('email');
    	$query_count = Password_Recovery::queryCountOnEmail ($email);
		
		// лимит запросов на e-mail
		if ($query_count >= Password_Recovery::MAX_QUERY_PER_EMAIL)
		{
		    $this->_output->send ('data', array (
		    	'error'	=> true
		    ));
		    $this->_template = 'Widget/Password/Recovery/sendCode/error_email_limit.tpl';
		    return null;
		}
		
		$user = IcEngine::$modelManager->modelBy (
		    'User',
		    Query::instance ()
		    ->where ('email', $email)
		);
		
		if (!$user)
		{
			$this->_output->send ('data', array (
			    'error'	=> true
			));
			$this->_template = 'Widget/Password/Recovery/sendCode/error_email_not_found.tpl';
			return null;
		}
		
		// Всё правильно, создаем письмо с кодом
		if (!Password_Recovery::sendRecoveryEmail ($user->id, $email))
		{
		    $this->_template = 'Widget/Password/Recovery/sendCode/error_sendmail.tpl';
			return null;
		}
    }
    
}