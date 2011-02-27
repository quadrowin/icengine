<?php
/**
 * 
 * @desc Модель регистрации на сайте
 * @author Гурус
 * @package IcEngine
 *
 */
class Registration extends Model
{
	
	/**
	 * @desc Конфиг
	 * @var array
	 */	
	protected static $_config = array (
		/**
		 * Событие после подтверждения емейла
		 * @var function (Registration)
		 */
		'after_confirm'	=> null,
		
		/**
		 * Событие после создания регистрации
		 * @var function (Registration, array) boolean
		 */
		'after_create'	=> null,
		
		/**
		 * Автоактивация (не требует активации по email).
		 * @var boolean
		 */
		'auto_active'	=> false,
		
		/**
		 * @desc Автосоздание и активация пользователя.
		 * @var boolean 
		 */
		'auto_user'		=> true,
		
		/**
		 * Отсылать сообщение.
		 * @var boolean
		 */
		'sendmail'		=> true,
		
		/**
		 * Ограничение на количество регистраций с одного ИП в день
		 * @var integer
		 */
		'ip_day_limit'	=> 20,
		
		/**
		 * Поля.
		 * @var array
		 */
		'fields'	=> array (
			'email'		=> array (
				'type'	=> 'string',
				'minLength'	=> 5,
				'maxLength'	=> 40,
				'value'		=> 'input',
				'filters'	=> 'Trim',
				'validators'	=> array (
					'Registration_Email'
				)
			),
			'password'	=> array (
				'type'	=> 'string',
				'minLength'	=> 6,
				'maxLength'	=> 250,
				'value'	=> 'input',
				'validators'	=> array (
					'Registration_Password'
				)
			),
			'ip'	=> array (
				'maxTries'	=> 10,
				'value'		=> array ('Request', 'ip'),
				'validators'	=> array (
					'Registration_Ip_Limit'
				)
			)
		)
	);
	
	/**
	 * @desc Автоактивация пользователя
	 */
	public function _autoUserActivate ()
	{
		if (!$this->User || !$this->User->key ())
		{
			Loader::load ('Zend_Exception');
			throw new Zend_Exception ('User unexists.');
		}
		
		$this->User->update (array (
			'active'	=> 1
		));
		
		if (self::config ()->after_confirm)
		{
			Loader::load (self::$_config ['after_confirm'][0]);
			call_user_func (
				self::$_config ['after_confirm']->__toArray (),
				$this
			);
		}
	}
	
	/**
	 * @desc Автосоздание пользователя после регистрации.
	 * @param array|Objective $data 
	 * @return User
	 */
	protected static function _autoUserCreate ($data)
	{
		return User::create (
			$data ['email'], $data ['password'], 
			self::config ()->autoactive, $data
		);
	}
	
	/**
	 * @desc Возвращает регистрацию по уникальному коду.
	 * @param string $code
	 * @return Registration|null
	 */
	public static function byCode ($code)
	{
		return IcEngine::$modelManager->modelBy (
			__CLASS__,
			Query::instance ()
			->where ('code', $code) 
		);
	}
	
	/**
	 * @desc Конфиг регистрации.
	 * @return Objective
	 */
	public static function config ()
	{
		if (is_array (self::$_config))
		{
			Loader::load ('Config_Manager');
			self::$_config = Config_Manager::get (__CLASS__, self::$_config);
		}
		return self::$_config;
	}
	
	/**
	 * Ссылка на подтверждение регистрации
	 * @return string
	 */
	public function confirmHref ()
	{
		return '/registration/' . $this->code;
	}
	
	/**
	 * @desc Завершение процесса регистрации, активация пользователя.
	 * @return Registration
	 */
	public function finish ()
	{
		$this->update (array (
			'finished'		=> 1,
			'finishTime'	=> date ('Y-m-d H:i:s')
		));
		
		if ($this->config ()->auto_user)
		{
			$this->_autoUserActivate ();
		}
		
		Loader::load ('Message_After_Registration_Finish');
		Message_After_Registration_Finish::push ($this);
		
		return $this;
	}
	
	/**
	 * @desc Возвращает новый уникальный код для активация по емейл.
	 * @param integer $id Первичный ключ регистрации.
	 * @return string
	 */
	public static function generateUniqueCode ($id)
	{
		return
			chr (rand (ord ('a'), ord ('z'))) . $id . 'a' .
			md5 (time ()) . md5 (rand (12345678, 87654321));
	}
	
	/**
	 * @desc Регистрация.
	 * @param array|Objective $data
	 * @return Registration
	 */
	public static function register ($data)
	{
		$registration = new Registration (array (
			'User__id'		=> 0,
			'email'			=> $data ['email'],
			'time'			=> Helper_Date::toUnix (),
			'ip'			=> Request::ip (),
			'day'			=> Helper_Date::eraDayNum (),
			'finished'		=> 0,
			'finishTime'	=> '2000-01-01 00:00:00',
			'code'			=> ''
		));
		$registration->save ();
		
		if (self::config ()->auto_user)
		{
			$user = self::_autoUserCreate ($data);
			
			Loader::load ('Message_After_Registration_Start');
			Message_After_Registration_Start::push ($registration);
			
			$registration->update (array (
				'User__id'	=> $user->id,
				'code'		=> self::generateUniqueCode ($user->id)
			));
		}
		else
		{
			Loader::load ('Message_After_Registration_Start');
			Message_After_Registration_Start::push ($registration);
		}
		
		if (self::config ()->after_create)
		{
			Loader::load (self::$_config ['after_create'][0]);
			if (
				!call_user_func (
					self::$_config ['after_create']->__toArray (), 
					$registration, $data
				)
			)
			{
				$registration->delete ();
				$user->delete ();
				return null;
			};
		}
		
		if (self::$_config ['sendmail'])
		{
			Loader::load ('Mail_Message');
			$message = Mail_Message::create (
				'user_register', 
				$data ['email'], $data ['email'],
				array (
					'email'		=> $data ['email'],
					'password'	=> $data ['password'],
					'time'		=> $registration->time,
					'code'		=> $registration->code,
					'href'		=> $registration->confirmHref ()
				),
				$user->id
			);
			$message->send ();
		}
		
		return $registration;
	}
	
	/**
	 * 
	 * @param Objective $data
	 * 		string ['email'] емейл
	 * 		string ['password'] пароль
	 * @return Registration|array Модель регистрации или ошибка.
	 */
	public static function tryRegister (Objective $data)
	{
		Helper_Form::filter ($data, self::config ()->fields);
		$result = Helper_Form::validate ($data, self::$_config ['fields']);
		
		if (is_array ($result))
		{
			return $result;
		}
		
		Helper_Form::unsetIngored ($data, self::$_config ['fields']);
		
		$reg = self::register ($data);
		
		return 
			$reg ? 
			$reg : array ('unknown' => 'Data_Validator_Registration/unknown');
	}
	
}