<?php

Loader::load ('Registration');
/**
 * 
 * @desc Контроллер регистрации
 * @author Гурус
 * @package IcEngine
 *
 */
class Controller_Registration extends Controller_Abstract
{
	
	/**
	 * @desc Последняя обработанная регистрация
	 * @var Registration
	 */
	public $registration;
	
	/**
	 * @desc Начало регистрации
	 */
	public function index ()
	{
		if (User::authorized ())
		{
			// Пользователь уже зарегистрирован
			Loader::load ('Header');
			Header::redirect ('/');
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
			Loader::load ('Header');
			Header::redirect ('/');
			return;
		}
		
		$registration = Registration::byCode (
			$this->_input->receive ('code')
		);
		
		if (!$registration)
		{
			$this->_dispatcherIteration->setClassTpl (
				__METHOD__,
				'/fail_code_uncorrect'
			);
			return false;	
		}
		elseif ($registration->finished)
		{
			$this->_dispatcherIteration->setClassTpl (
				__METHOD__,
				'/fail_already_finished'
			);
			return false;
		}
		
		$registration->finish ();
		
		return true;
	}
	
	public function postForm ()
	{
		Loader::load ('Helper_Form');
		$data = Helper_Form::receiveFields (
			$this->_input, 
			Registration::config ()->fields
		);
		
		$registration = Registration::tryRegister ($data);
		$this->_output->send ('registration', $registration);
		
		if (is_array ($registration))
		{
			// произошла ошибка
			
			$this->_dispatcherIteration->setClassTpl (reset ($registration));
			
			$this->_output->send (array (
				'registration'	=> $registration,
				'data'			=> array (
					'field'			=> key ($registration),
					'error'			=> current ($registration)
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