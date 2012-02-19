<?php
/**
 * 
 * @desc Контроллер регистрации
 * @author Гурус
 * @package IcEngine
 *
 */
Loader::load ('Registration');

class Controller_Registration extends Controller_Abstract
{
	
	/**
	 * @desc Начало регистрации
	 */
	public function index ()
	{
		if (User::authorized ())
		{
			// Пользователь уже зарегистрирован
			Loader::load ('Helper_Header');
			Helper_Header::redirect ('/');
			die ();
		}
	}
	
	/**
	 * @desc Подтверждение email
	 * @return boolean true, если регистрация закончилась успешно. Иначе false.
	 */
	public function emailConfirm ()
	{
		if (User::authorized ())
		{
			Loader::load ('Helper_Header');
			Helper_Header::redirect ('/');
			return;
		}
		
		$registration = Registration::byCode (
			$this->_input->receive ('code')
		);
		
		if (!$registration)
		{
			$this->_task->setClassTpl (__METHOD__, 'fail_code_uncorrect');
			return false;	
		}
		elseif ($registration->finished)
		{
			$this->_task->setClassTpl (__METHOD__, 'fail_already_finished');
			return false;
		}
		
		$registration->finish ();
		
		return true;
	}
	
	/**
	 * @desc Подтверждение email и авторизация
	 */
	public function emailConfirmAndAuthorization ()
	{
		$this->_task->setTemplate (null);
		
		if (User::authorized ())
		{
			Loader::load ('Helper_Header');
			Helper_Header::redirect ('/');
			return;
		}
		
		$registration = Registration::byCode (
			$this->_input->receive ('code')
		);
		
		if (!$registration) {
			$this->_output->send (array (
				'data'		=> array (
					'error'	=> 'codeInvalid'
				)
			));
			return;
		}
		
		$registration->finish ();
		
	        $user = $registration->User;
			
		$user->authorize ();
			
		$this->_output->send (array (
			'data'		=> array (
				'userId'	=> $user->key (),
				'cityId'	=> City::id ()
			)
		));
	}
	
	public function postForm ()
	{
		Loader::load ('Helper_Form');
		
        $fields = Config_Manager::get ('Registration')->fields;
        
        $data = new Objective();
        
        if ($fields) {
            $data = Helper_Form::receiveFields (
                $this->_input, 
                $fields
            );
        }

        $registration = Registration::tryRegister ($data);
		
		if (is_array ($registration))
		{
			// произошла ошибка
			reset($registration);
            $error = explode('/', current ($registration));

			$this->_task->setClassTpl ($error[0], $error[1]);
			
			$this->_output->send (array (
				'registration'	=> $registration,
				'data'			=> array (
                    'field'			=> key ($registration),
                    'error'		=> current ($registration)
				)
			));
			
			return ;
		}
		
		$this->_output->send (array (
			'registration'	=> $registration,
			'data'			=> array (
				'removeForm'	=> true
			)
		));
	}
	
	public function success ()
	{
		
	}
	
}