<?php

namespace Ice;

Loader::load ('Registration');

/**
 *
 * @desc Контроллер регистрации
 * @author Yury Shvedov
 * @package Ice
 *
 */
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
		$data = Helper_Form::receiveFields (
			$this->_input,
			Config_Manager::get ('Registration')->fields
		);

		$registration = Registration::tryRegister ($data);
		$this->_output->send ('registration', $registration);

		if (is_array ($registration))
		{
			// произошла ошибка

			$this->_task->setClassTpl (reset ($registration));

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