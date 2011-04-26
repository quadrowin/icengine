<?php
/**
 * 
 * @desc Контроллер востановления пароля.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Password_Recovery extends Controller_Abstract
{
	
	public function index ()
	{
		Loader::load ('Password_Recovery');
		
		$code = Request::get ('code');
		
		if ($code)
		{
			$recovery = Password_Recovery::byCode ($code);
			if ($recovery && $recovery->active)
			{
				$recovery->startSession ();
				
				IcEngine::$application
					->frontController
					->getDispatcher ()
					->currentIteration ()
					->setTemplate (
						Helper_Action::path (__METHOD__, '/code_ok')
					);
				return ;
			}
			else
			{
				IcEngine::$application
					->frontController
					->getDispatcher ()
					->currentIteration ()
					->setTemplate (
						Helper_Action::path (__METHOD__, '/code_fail')
					);
				return ;
			}
		}
		
		Password_Recovery::resetSession ();
	}

	public function change ()
	{
		$password = $this->_input->receive ('password');
		if (strlen ($password) < 3)
		{
			$this->_output->send ('data', array (
				'error'	=> true
			));
			
			$this->_dispatcherIteration->setTemplate ( 
				Helper_Action::path (__METHOD__, '/error_short_password')
			);
				
			return ;
		}
		
		Loader::load ('Password_Recovery');
		$recovery = Password_Recovery::fromSession ();
		
		if (!$recovery)
		{
			$this->_output->send ('data', array (
				'error'	=> true
			));
			
			$this->_dispatcherIteration->setTemplate ( 
				Helper_Action::path (__METHOD__, '/error_recovery_not_found')
			);
				
			return ;
		}
		
		// меняем пароль и делаем неактивной смену
		$recovery->update (array (
			'active'	=> 0
		));
		
		$recovery->User->update (array (
			'password'	=> $password
		));
		
		$this->_output->send ('data', array (
			'removeForm'	=> true
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
			
			$this->_dispatcherIteration->setTemplate (
				Helper_Action::path (__METHOD__, '/error_email_limit')
			);
				
			return ;
		}
		
		$user = Model_Manager::byQuery (
			'User',
			Query::instance ()
				->where ('email', $email)
		);
		
		if (!$user)
		{
			$this->_output->send ('data', array (
				'error'	=> true
			));
			
			$this->_dispatcherIteration->setTemplate ( 
				Helper_Action::path (__METHOD__, '/error_email_not_found')
			);
			
			return ;
		}
		
		$this->_output->send ('data', array (
			'removeForm' => true
		));
		
		// Всё правильно, создаем письмо с кодом
		if (!Password_Recovery::sendRecoveryEmail ($user->id, $email))
		{
			$this->_dispatcherIteration->setTemplate (
				Helper_Action::path (__METHOD__, '/error_sendmail')
			);
				
			return ;
		}
	}
	
}