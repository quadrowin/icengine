<?php
/**
 * 
 * @desc Контроллер для работы с Loginza
 * @author Юрий Шведов
 * @package Ice_Vipgeo
 *
 */
class Controller_Loginza extends Controller_Abstract
{
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	public $config = array (
		// Адрес логинзы, где хранится результат авторизации
		'loginza_url'				=> 'http://loginza.ru/api/authinfo?token={$token}',
		// Редирект при регистрации нового пользователя
		'registration_redirect'		=> '/registration/?token={$token}',
		// Редирект после авторизации существующего пользователя
		'authorization_redirect'	=> null
	);
	
	/**
	 * @desc Перенаправление пользователя с ключом результата авторизации.
	 */
	public function token ()
	{
		list (
			$redirect,
			$token
		) = $this->_input->receive (
			'redirect',
			'token'
		);
		
		$url = str_replace (
			'{$token}',
			$token,
			$this->config ()->loginza_url
		);
		$result = file_get_contents ($url);
		$data = json_decode ($result, true);
		
		if (isset ($data ['error_type']))
		{
			// Не удалось авторизоваться
			$this->_output->send (array (
				'error'	=> $data ['error_type'],
				'data'	=> array (
					'error_type'	=> $data ['error_type'],
					'error_message'	=> $data ['error_message']
				)
			));
		}
		else
		{
			// Успешная авторизация
			Loader::load ('Loginza_Token');
			$loginza_token = new Loginza_Token (array (
				'time'		=> Helper_Date::toUnix (),
				'token'		=> $token,
				'result'	=> $result,
				'email'		=> isset ($data ['email']) ? $data ['email'] : ''
			));
			
			if ($this->config ['registration_redirect'])
			{
				$redirect = str_replace (
					'{$token}',
					$token,
					$this->config ['registration_redirect']
				);
			}
		}
		
		Loader::load ('Header');
		Header::redirect ($redirect ? $redirect : '/');
		die ();
	}
	
}