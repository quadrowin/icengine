<?php

class Registration extends Model
{

    const OK  = 'ok';     // Успешно
    
    const FAIL = 'fail';      
	
    /**
     * 
     * @var array
     */	
	public static $config = array (
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
	    'autoactive'	=> false,
	    
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
	        	'value'	    => array ('Request', 'ip'),
	            'validators'	=> array (
	        		'Registration_Ip_Limit'
	            )
	        )
	    )
	);
	
	public static $scheme = array (
		Query::FROM	    => __CLASS__,
		Query::INDEX	=> array (
			array ('email'),
			array ('day', 'ip'),
			array ('code')
		)
	);
	
	public static function loadConfig ()
	{
        Loader::load ('Config_Manager');
        $cfg = Config_Manager::loadConfig ('Registration');
        self::$config = $cfg->mergeConfig (self::$config);
	}
	
	/**
	 * @param integer $id 
	 * @return string
	 */
	public static function generateUniqueCode ($id)
	{
		return
			chr (rand (ord ('a'), ord ('z'))) . $id . 'a' .
			md5 (time ()) . md5 (rand (12345678, 87654321));
	}
	
	/**
	 * Возвращает регистрацию по уникальному коду.
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
	 * Ссылка на подтверждение регистрации
	 * @return string
	 */
	public function confirmHref ()
	{
		return '/registration/' . $this->code;
	}
	
	/**
	 * Завершение процесса регистрации, активация пользователя.
	 */
	public function finish ()
	{
		$this->update (array (
			'finished'		=> 1,
			'finishTime'	=> date ('Y-m-d H:i:s')
		));
		
		if ($this->User)
		{
			$this->User->update (array (
				'active'	=> 1
			));
			
			if (self::$config ['after_confirm'])
			{
			    Loader::load (self::$config ['after_confirm'][0]);
			    call_user_func (
			        self::$config ['after_confirm'],
			        $this);
			}
		}
		else
		{
		    Loader::load ('Zend_Exception');
		    throw new Zend_Exception ('Пользователя не существует.');
		}
	}
	
	/**
	 * 
	 * @param array $data
	 * @param boolean $send_mail
	 * @return Registration
	 */
	public static function register (array $data, $send_mail = true)
	{
		$user = User::create (
		    $data ['email'], $data ['password'], 
		    self::$config ['autoactive'], $data);
		
		Loader::load ('Helper_Date');
		$registration = new Registration (array (
			'User__id'	=> $user->id,
			'email'		=> $data ['email'],
			'time'		=> date (Helper_Date::UNIX_FORMAT),
			'ip'		=> Request::ip (),
			'day'		=> Helper_Date::eraDayNum (),
			'finished'		=> 0,
			'finishTime'	=> '2000-01-01 00:00:00',
			'code'			=> self::generateUniqueCode ($user->id)
		));
		$registration->save ();
		
		if (self::$config ['after_create'])
		{
		    Loader::load (self::$config ['after_create'][0]);
		    if (
		        !call_user_func (self::$config ['after_create'], 
		            $registration, $data)
		    )
	        {
	            $registration->delete ();
	            $user->delete ();
	            return null;
	        };
		}
		
		if ($send_mail)
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
	 * @param array $data
	 * 		string ['email'] емейл
	 * 		string ['password'] пароль
	 * @return integer
	 */
	public static function tryRegister (array $data)
	{
	    $result = self::validate ($data);
	    
	    if ($result !== true)
	    {
	        return $result;
	    }
	    
	    $ok = self::register ($data, self::$config ['sendmail']);
	    
	    return $ok ? self::OK : self::FAIL;
	}
	
	/**
	 * Проверка полей формы регистрации
	 * @param array $data
	 * @return mixed
	 * 		Registration::OK если валидация пройдена успешно,
	 * 		иначе код ошибки.
	 */
	public static function validate (array &$data)
	{
		Loader::load ('Helper_Form');
		return Helper_Form::validate ($data, self::$config ['fields']);
	}
	
}

Registration::loadConfig ();