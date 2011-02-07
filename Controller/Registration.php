<?php

Loader::load ('Registration');

class Controller_Registration extends Controller_Abstract
{
	
	/**
	 * Последняя обработанная регистрация
	 * @var Registration
	 */
	public $registration;
	
	/**
	 * Начало регистрации
	 */
	public function index ()
	{
		if (User::authorized ())
		{
			Loader::load ('Header');
			Header::redirect ('/');
			die ();
		}
	}
	
	/**
	 * Подтверждение email
	 * @return boolean
	 * 		True, если регистрация закончилась успешно.
	 * 		Иначе false.
	 */
	public function emailConfirm ()
	{
		if (User::authorized ())
		{
			Loader::load ('Header');
			Header::redirect ('/');
			return;
		}
		
		$this->registration = Registration::byCode (
			$this->_input->receive ('code'));
		
		if (!$this->registration)
		{
			$this->_dispatcherIteration->setTemplate (
				Helper_Action::path (__METHOD__, '/fail_code_uncorrect')
			);
			return false;	
		}
		elseif ($this->registration->finished)
		{
			$this->_dispatcherIteration->setTemplate (
				Helper_Action::path (__METHOD__, '/fail_already_finished')
			);
			return false;
		}
		
		$this->registration->finish ();
		return true;
	}
	
	public function postForm ()
	{
		Loader::load ('Helper_Form');
		$data = Helper_Form::receiveFields ($this->_input, 
			Registration::$config ['fields']);
		
		$valid = Registration::tryRegister ($data);
		$this->_output->send ('valid', $valid);
		
		if (is_array ($valid))
		{
			$this->_dispatcherIteration->setTemplate (
				str_replace (array ('::', '_'), '/', reset ($valid)) . 
				'.tpl'
			);
			$this->_output->send ('data', array (
				'field'	=> key ($valid),
				'error'	=> current ($valid)
			));
		}
		else
		{
			$this->_output->send ('data', array (
				'removeForm'	=> true
			));
		}
	}
	
	public function success ()
	{
		
	}
	
}