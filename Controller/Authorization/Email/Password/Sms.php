<?php
/**
 * 
 * @desc Контроллер для авторизации по емейлу, паролю и смс
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Authorization_Email_Password_Sms extends Controller_Abstract
{
	
	/**
	 * @param Аттрибут с кодом, высланным в СМС
	 * @var string
	 */
	const SMS_CODE_ATTR = 'smsAuthCode';
	
	/**
	 * @param Аттрибут - количество отправленных СМС
	 * @var string
	 */
	const SMS_SEND_COUNTER_ATTR = 'smsAuthSendCount';
	
	/**
	 * @param Аттрибут со временем последней отправки кода
	 * @var string
	 */
	const SMS_SEND_TIME_ATTR = 'smsAuthSendTime';
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected $_config = array (
		// Авторизовать только пользователей, имеющих одну из ролей.
		// Роли перечисляются через запятую.
		'auth_roles_names'			=> 'admin',
		'code_min_length'			=> 4,
		'code_max_length'			=> 6,
		'mail_template'				=> 'old_admin_auth_sms',
		'mail_provider_id'			=> 5,
		'mail_provider_params'		=> array (
			'providers'	=> 'Sms_Dcnk,Sms_Littlesms,Sms_Yakoon'
		),
		// Лимит смс в 1 минуту
		'sms_send_limit_1m'			=> 1,
		// Лимит смс на 10 минут
		'sms_send_limit_10m'		=> 5,
		// Колбэки на авторизацию и выход
		'authorization_function'	=> 'Helper_Old_Admin_Authorization::authorize',
		'unauthorization_function'	=> 'Helper_Old_Admin_Authorization::unauthorize'
	);
	
	/**
	 * @desc Авторизация
	 */
	protected function _authorize (User $user)
	{
		if (!$this->config ()->authorization_function)
		{
			return ;
		}
		
		list ($class, $method) = explode (
			'::',
			$this->config ()->authorization_function
		);
		
		Loader::load ($class);
		call_user_func (
			array ($class, $method),
			$user
		);
	}
	
	/**
	 * @desc Выход из админки
	 */
	protected function _unauthorize ()
	{
		if (!$this->config ()->unauthorization_function)
		{
			return ;
		}
		
		list ($class, $method) = explode (
			'::',
			$this->config ()->unauthorization_function
		);
		
		Loader::load ($class);
		call_user_func (array ($class, $method));
	}
	
	/**
	 * @desc Проверка на принадлежность пользователя к необходимой роли
	 * @param User $user
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
	 * @see Controller_Abstract::index()
	 */
	public function index ()
	{
		// Просто форма авторизации
	}
	
	/**
	 * @desc Авторизация
	 * @param string $name
	 * @param string $pass
	 * @param string $code
	 */
	public function login ()
	{
		list (
			$login,
			$password,
			$sms_code,
			$redirect
		) = $this->_input->receive (
			'name',
			'pass',
			'code',
			'href'
		);
		
		if (!$sms_code)
		{
			return $this->replaceAction ($this, 'sendSmsCode');
		}
		
		/**
		 * @desc Авторизующийся пользователь
		 * @var User
		 */
		$user = IcEngine::$modelManager->modelBy (
			'User',
			Query::instance ()
			->where ('email', $login)
			->where ('password', $password)
			->where ('md5(`password`) = md5(?)', $password)
		);
		
		if (!$user)
		{
			$this->_sendError (
				'password incorrect',
				__METHOD__,
				'/passwordIncorrect'
			);
			return ;
		}
		
		if (!$user->active)
		{
			$this->_sendError (
				'user unactive',
				__METHOD__,
				'/userUnactive'
			);
			return ;
		}
		
		if (!$this->_userHasRole ($user))
		{
			$this->_sendError (
				'access denied',
				__METHOD__,
				'/accessDenied'
			);
			return ;
		}
		
		$current_code = $user->attr (self::SMS_CODE_ATTR);
		if (!$current_code || $current_code != $sms_code)
		{
			$this->_sendError (
				'code fail',
				__METHOD__,
				'/codeFail'
			);
			return ;
		}
		
		// Сбрасываем счетчик СМС.
		$user->attr (array (
			self::SMS_SEND_COUNTER_ATTR	=> 0,
			self::SMS_CODE_ATTR			=> ''
		));
		$user->authorize ();
		$this->_authorize ($user);
		
		Loader::multiLoad ('Helper_Uri');
		$redirect = Helper_Uri::validRedirect ($redirect);
		$this->_output->send (array (
			'redirect'		=> $redirect,
			'data'	=> array (
				'redirect'	=> $redirect
			)
		));
	}
	
	/**
	 * @desc Деавторизация
	 */
	public function logout ()
	{
		$this->_unauthorize ();
		
	}
	
	/**
	 * @desc Отправка СМС кода
	 */
	public function sendSmsCode ()
	{
		list (
			$login,
			$password
		) = $this->_input->receive (
			'name',
			'pass'
		);
		
		$user = IcEngine::$modelManager->modelBy (
			'User',
			Query::instance ()
			->where ('email', $login)
			->where ('password', $password)
			->where ('md5(`password`) = md5(?)', $password)
		);
		
		if (!$user)
		{
			$this->_sendError (
				'password incorrect',
				__METHOD__,
				'/passwordIncorrect'
			);
			return ;
		}
		
		if (!$user->active)
		{
			$this->_sendError (
				'user unactive',
				__METHOD__,
				'/userUnactive'
			);
			return ;
		}
		
		if (!$user->phone)
		{
			$this->_sendError (
				'no phone',
				__METHOD__,
				'/noPhone'
			);
			return ;
		}
		
		if (!$this->_userHasRole ($user))
		{
			$this->_sendError (
				'access denied',
				__METHOD__,
				'/accessDenied'
			);
			return ;
		}
		
		$count = $user->attr (self::SMS_SEND_COUNTER_ATTR);
		$time = Helper_Date::toUnix ();
		$last_time = $user->attr (self::SMS_SEND_TIME_ATTR);
		$delta_time = Helper_Date::secondsBetween ($last_time);
		
		if (
			(
				$count >= $this->config ()->sms_send_limit_1m &&
				$delta_time < 60
			) ||
			(
				$count >= $this->config ()->sms_send_limit_10m &&
				$delta_time < 600
			)
		)
		{
			$this->_sendError (
				'sms limit',
				__METHOD__,
				'/smsLimit'
			);
			return ;
		}
		
		Loader::load ('Helper_Activation');
		$code = Helper_Activation::generateNumeric (
			$this->config ()->code_min_length,
			$this->config ()->code_max_length
		);
		
		if (!$code)
		{
			$this->_sendError (
				'code generation fail',
				__METHOD__,
				'/accessDenied'
			);
			return ;
		}
		
		$user->attr (array (
			self::SMS_CODE_ATTR				=> $code,
			self::SMS_SEND_TIME_ATTR		=> $time,
			self::SMS_SEND_COUNTER_ATTR		=> $count + 1
		));
		
		Loader::load ('Mail_Message');
		$message = Mail_Message::create (
			$this->config ()->mail_template,
			$user->phone,
			$user->name,
			array (
				'code'	=> $code,
				'time'	=> $time
			),
			$user->id,
			$this->config ()->mail_provider_id,
			$this->config ()->mail_provider_params->__toArray ()
		);
		
		$message->save ()->send ();
		$this->_output->send (array (
			'message'	=> $message,
			'time'		=> $time
		));
	}
	
}