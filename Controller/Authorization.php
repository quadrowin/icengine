<?php
/**
 * 
 * @desc Контроллер авторизации
 * @author Юрий
 * @package IcEngine
 *
 */
class Controller_Authorization extends Controller_Abstract
{
	
	/**
	 * @desc Редирект по умолчанию после авторизация/логаута.
	 * @var unknown_type
	 */
	const DEFAULT_REDIRECT = '/';
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	public $config = array (
		// Возможно авторизация через СМС.
		'sms_auth_enable'			=> false,
		// Префикс активации при авторизации через СМС.
		// Этот префикс не будет отсылаться в сообщении, но необходим
		// для уникальности ключей активации.
		'sms_auth_prefix'			=> 'sms_auth.',
		// Время действительности кода активации - 1 час.
		'sms_auth_expiration'		=> 3600,
		// Шаблон смски.
		'sms_auth_mail_template'	=> 'sms_activate',
		// Провайдер, отправляющий СМС
		'sms_auth_provider'			=> 'First_Success',
		// Параметры СМС провайдера
		'sms_auth_provider_config'	=> array (
			'providers'	=> 'Sms_Dcnk,Sms_Littlesms,Sms_Yakoon'
		),
		// Режим тестирования СМС (сообщения не отправляются и код отправляется
		// в ответ на POST запрос отправки СМС)
		'sms_auth_test_mode'		=> false
	);
	
	/**
	 * Досутп закрыт для текущего пользователя
	 */
	function accessDenied ()
	{
		$this->_output->send ('user', User::getCurrent ());
	}
	
	public function authDialog ()
	{
	
	}
	
	/**
	 * @desc Проверка на существования пользователя с таким Email.
	 * Используется в диалоге входа/регистрации.
	 */
	public function checkEmail ()
	{
		$email = $this->_input->receive ('email');
		
		$exists = DDS::execute (
			Query::instance ()
			->select ('id')
			->from ('User')
			->where ('email', $email)
		)->getResult ()->asValue ();
		
		$this->_output->send ('data', array (
			'email'		=> $email,
			'exists'	=> (bool) $exists
		));
		
		$this->_dispatcherIteration->setTemplate (null);
	}
	
	/**
	 * @desc Авторизация.
	 * @param string login Логин.
	 * @param string password Пароль.
	 * @param string redirect [optional] Редирект после успешной авторизации.
	 */
	public function login ()
	{
		$login = $this->_input->receive ('login');
		
		if ($this->config ()->sms_auth_enable)
		{
			Loader::load ('Helper_Phone');
			$phone = Helper_Phone::parseMobile ($login);
			if ($phone)
			{
				return $this->replaceAction ($this, 'loginBySms');
			}
		}
		
		list (
			$password,
			$redirect
		) = $this->_input->receive (
		 	'password',
			'redirect'
		);
		
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
			$this->_output->send ('error', 'Password incorrect');
			$this->_dispatcherIteration->setClassTpl (
				__METHOD__,
				'/password_incorrect'
			);
		}
	}
	
	/**
	 * @desc Авторизация через СМС
	 */
	public function loginBySms ()
	{
		if (!$this->config ()->sms_auth_enable)
		{
			$this->_output->send ('error', 'Sms auth disabled');
			$this->_dispatcherIteration->setClassTpl (
				__METHOD__, '/fail'
			);
			return;
		}
		
		list (
			$phone,
			$activation_id,
			$clear_code
		) = $this->_input->receive (
			'login',
			'sms_session_id',
			'sms_session_code'
		);
		
		$code = $this->config ()->sms_auth_prefix;
		
		$activation = IcEngine::$modelManager->modelByKey (
			'Activation',
			$activation_id
		);
		
		if (!$activation || $activation->code != $code)
		{
			$this->_sendError (
				'incorrect code',
				__METHOD__,
				'/incorrect_code'
			); 
			return;
		}
	}
	
	/**
	 * @desc Авторизация или регистрация.
	 */
	public function loginOrReg ()
	{
		$login = $this->_input->receive ('login');
		
		$login_exists = DDS::execute (
			Query::instance ()
			->select ('id')
			->from ('User')
			->where ('email', $login)
		)->getResult ()->asValue ();
		
		if ($login_exists)
		{
			// Авторизация
			return $this->replaceAction ($this, 'login');
		}

		// Регистрация
		return $this->replaceAction ('Registration', 'postForm');
	}
	
	/**
	 * @desc Выход.
	 */
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
	
	/**
	 * @desc Авторизация через отправку СМС с кодом
	 */
	public function sendSmsCode ()
	{
		list (
			$phone,
			$code_type
		) = $this->_input->receive (
			'phone',
			'code_type'
		);
		
		if (!$phone || !$code_type)
		{
			$this->_output->send ('error', 'empty phone or code_type');
			$this->_dispatcherIteration->setClassTpl (__METHOD__, '/fail');
			return;
		}
		
		$cfg = $this->config ();
		
		if (!$cfg->sms_auth_enable)
		{
			$this->_output->send ('error', 'sms disabled');
			$this->_dispatcherIteration->setClassTpl (__METHOD__, '/fail');
			return;
		}
		
		Loader::load ('Helper_Activation');
		$code = Helper_Activation::newShortCode ($cfg ['sms_auth_prefix']);
		
		if (!$code)
		{
			$this->_output->send ('error', 'error on activation create');
			$this->_dispatcherIteration->setClassTpl (__METHOD__, '/fail');
			return;
		}
		
		$clear_code = substr ($code, strlen ($cfg ['sms_auth_prefix']));
		
		Loader::load ('Activation');
		$activation = Activation::create (
			$code,
			Helper_Date::toUnix (time () + $cfg ['sms_auth_expiration'])
		);
		
		if (!$activation)
		{
			$this->_output->send ('error', 'error on activation create');
			$this->_dispatcherIteration->setClassTpl (__METHOD__, '/fail');
			return;
		}
		
		$provider_name = $cfg ['sms_auth_provider'];
		
		/**
		 * @desc Провайдер
		 * @var Mail_Provider_Abstract
		 */
		$provider = IcEngine::$modelManager->modelBy (
			'Mail_Provider',
			Query::instance ()
			->where ('name', $provider_name)
		);
		
		if (!$provider)
		{
			$this->_output->send (
				'error',
				'provider not found: ' . $provider_name
			);
			$this->_dispatcherIteration->setClassTpl (__METHOD__, '/fail');
			return;
		}
		
		$user = IcEngine::$modelManager->modelBy (
			'User',
			Query::instance ()
			->where ('phone', $phone)
		);
		
		Loader::load ('Mail_Message');
		$message = Mail_Message::create (
			$cfg ['sms_auth_mail_template'],
			$phone,
			$user ? $user->name : $phone,
			array (
				'code'			=> $clear_code,
				'session_id'	=> $activation->id
			),
			$user ? $user->id : 0,
			$provider->id,
			$cfg ['sms_auth_provider_config']->__toArray ()
		)->save ();
		
		if (!$cfg->sms_auth_test_mode)
		{
			// Если это не тестовый режим, то отправляем
			if (!$message->send ())
			{
				$this->_output->send ('error', 'mail send error');
				$this->_dispatcherIteration->setClassTpl (__METHOD__, '/fail');
				return;
			}
		}
		
		$this->_output->send (array (
			'activation'	=> $activation,
			'data'			=> array (
				'activation_id'		=> $activation->id,
				'phone_registered'	=> (bool) $user,
				'code'				=> $cfg->sms_auth_test_mode ? $clear_code : ''
			)
		));
	}
	
}