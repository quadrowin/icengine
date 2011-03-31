<?php
/**
 * 
 * @desc Аавторизация через отправку пользователю СМС сообщения с кодом.
 * @author Юрий Шведов
 * @package IcEngine
 * 
 */
Loader::load ('Authorization_Abstract');
class Authorization_Login_Password_Sms extends Authorization_Abstract
{
	
	/**
	 * @desc Config
	 * @var array
	 */
	protected $_config = array (
		// Авторизовать только пользователей, имеющих одну из ролей.
		// Роли перечисляются через запятую.
		'auth_roles_names'			=> 'admin',
	
		// Функция, вызываемая после успешной авторизации
		'authorization_callback'	=> null,
	
		// Минимальная длина кода
		'code_min_length'		=> 4,
	
		// Максимальная длина кода
		'code_max_length'		=> 6,
	
		// Валидатор логина
		'login_validator'		=> 'Email',
	
		// Время действительности СМС
		'sms_expiration'		=> 3600,
		// префикс кода в БД
		'sms_prefix'			=> 'smsauth.',
		// Провайдер СМСок
		'sms_provider'			=> 'First_Success',
		// Параметры для провайдера
		'sms_provider_params'	=> array (
			'providers'			=> 'Sms_Littlesms,Sms_Dcnk,Sms_Yakoon'
		),
		// Шабон СМСок
		'sms_mail_template'		=> 'sms_activate',
		// Тестовый режим
		'sms_test_mode'			=> true,
		
		/**
		 * @desc можно перечислить логины, пароли и телефоны пользователей.
		 * Если этот параметр array, то пользователи, не указанные в этом 
		 * массиве не могут быть авторизованы этим методом.
		 * @tutorial
		 * 	'users'	=> array (
		 * 		'admin'	=> array (
		 * 			'active'	=> true,
		 * 			'password'	=> 'password',
		 * 			'phone'		=> '+7 123 456 78 90'
		 * 		)
		 * 	)
		 */ 
		'users'	=> false
	);
	
	/**
	 * @desc Авторизация
	 */
	protected function _authorize (User $user)
	{
		$user->authorize ();
		
		$config = $this->config ();
		
		if (!$config ['authorization_callback'])
		{
			return ;
		}
		
		list ($class, $method) = explode (
			'::',
			$config ['authorization_callback']
		);
		
		Loader::load ($class);
		call_user_func (
			array ($class, $method),
			$user
		);
	}
	
	/**
	 * @desc Дополнительная проверка пользователя перед началом авторизации
	 * до отправки кода СМС.
	 * @param User $user Пользователь.
	 * @param string $login Указанный логин.
	 * @param string $password Указанный пароль.
	 * @return boolean true, если нужно проверять дальше, иначе - false.
	 */
	protected function _prechekUser (User $user, $login, $password)
	{
		if (!$this->_userHasRole ($user))
		{
			return false;
		}
		
		$cfg_users = $this->config ()->users;
		
		if ($cfg_users === false)
		{
			// нет проверки
			return true;
		}
		
		// Приводим к нижнему регистру
		$login = strtolower ($login);
		
		return (
			isset ($cfg_users [$login]) &&
			$cfg_users [$login]['password']	== $password &&
			$cfg_users [$login]['phone']	== $user->phone &&
			$cfg_users [$login]['active']
		);
	}
	
	/**
	 * @desc Дополнительная проверка пользователя перед авторизацией после
	 * проверки кода СМС.
	 * @param User $user Подходящий пользователь.
	 * @param string $login Логин, указанный при авторизации.
	 * @param string $password Пароль.
	 * @return boolean true, если успешно, иначе - false.
	 */
	protected function _postcheckUser (User $user, $login, $password)
	{
		return $this->_prechekUser ($user, $login, $password);
	}
	
	/**
	 * @desc Проверка на принадлежность пользователя к необходимой роли
	 * @param User $user Пользователь
	 * @return boolean
	 */
	protected function _userHasRole (User $user)
	{
		$roles = explode (',', $this->config ()->auth_roles_names);
		
		if (!$roles)
		{
			// Ролей не задано, авторизуем всех
			return true;
		}
		
		foreach ($roles as $role)
		{
			$role = Acl_Role::byName ($role);
			if ($user->hasRole ($role))
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::authorize()
	 * @param string $data ['login']
	 * @param string $data ['password']
	 * @param integer $data ['activation_id']
	 * @param string $data ['activation_code']
	 */
	public function authorize ($data)
	{
		$user = Model_Manager::modelBy (
			'User',
			Query::instance ()
				->where ('login', $data ['login'])
				->where ('password', $data ['password'])
				->where ('md5(`password`)=md5(?)', $data ['password'])
				->where ('active', 1)
		);
		
		if (!$user)
		{
			return 'Data_Validator_Authorization_Password/invalid';
		}
		
		if (!$this->_postcheckUser($user, $data ['login'], $data ['password']))
		{
			return 'Data_Validator_Authorization_User/denied';
		}
		
		$prefix = $this->config ()->sms_prefix;
		
		$activation = Model_Manager::modelBy (
			'Activation',
			Query::instance ()
				->where ('code', $prefix . $data ['activation_code'])
				->where ('id', $data ['activation_id'])
				->where ('User__id', $user->id)
				->where ('finished', false)
		);
		
		if (!$activation || $activation->finished)
		{
			return 'Data_Validator_Activation_Code/invalid';
		}
		
		$activation->update (array (
			'finished'	=> 1
		));
		
		$this->_authorize ($user);
		
		return $user;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isRegistered()
	 */
	public function isRegistered ($login)
	{
		$user = Model_Manager::modelBy (
			'User',
			Query::instance ()
				->where ('login', $login)
		);
		
		return (bool) $user;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::isValidLogin()
	 */
	public function isValidLogin ($login)
	{
		Loader::load ('Data_Validator_Manager');
		return Data_Validator_Manager::validate (
			$this->config ()->login_validator,
			$login
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Authorization_Abstract::findUser()
	 */
	public function findUser ($data)
	{
		return Model_Manager::modelBy (
			'User',
			Query::instance ()
				->where ('login', $data ['login'])
		);
	}
	
	/**
	 * @desc Отправляет пользователю СМС для авторизации
	 * @param array $data
	 * @param string $data ['login']
	 * @param string $data ['password']
	 * @param User $data ['user']
	 * @return Activation
	 */
	public function sendActivationSms (array $data)
	{
		$user = $data ['user'];
		
		if ($user->login != $data ['login'])
		{
			return 'Data_Validator_Authorization_User/unexists';
		}
		
		if ($user->password != $data ['password'])
		{
			return 'Data_Validator_Authorization_Password/invalid';
		}
		
		if (!$user->active)
		{
			return 'Data_Validator_Authorization_User/unactive';
		}
		
		if (!$this->_prechekUser ($user, $data ['login'], $data ['password']))
		{
			return 'Data_Validator_Authorization_User/denied';
		}
		
		$config = $this->config ();
		
		Loader::load ('Helper_Activation');
		$clear_code = Helper_Activation::generateNumeric (
			$config ['code_min_length'],
			$config ['code_max_length']
		);
		
		$activation_code = $config ['sms_prefix'] . $clear_code;
		
		Loader::load ('Activation');
		$activation = Activation::create (array (
			'address'			=> $user->phone,
			'code'				=> $activation_code,
			'expirationTime'	=> 
				Helper_Date::toUnix (time () + $config ['sms_expiration']),
			'User__id'			=> $user->id
		));
		
		/**
		 * @desc Провайдер
		 * @var Mail_Provider_Abstract
		 */
		$provider = Model_Manager::modelBy (
			'Mail_Provider',
			Query::instance ()
				->where ('name', $config ['sms_provider'])
		);
		
		Loader::load ('Mail_Message');
		$message = Mail_Message::create (
			$config ['sms_mail_template'],
			$user->phone,
			$user->name,
			array (
				'code'			=> $clear_code,
				'session_id'	=> $activation->id
			),
			$user->id,
			$provider->id,
			$config ['sms_provider_params']->__toArray ()
		)->save ();
		
		if ($config ['sms_test_mode'])
		{
			echo 'sms test mode';
		}
		else
		{
			$message->send ();
		}
		
		return $activation;
	}
	
}