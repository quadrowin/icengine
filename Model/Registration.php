<?php

class Registration extends Model
{

    const OK                    = 1; // Успешно
    
	const FAIL_IP_LIMIT			= 20; // Лимит на 1 ip
	
	const FAIL_EMAIL_EMPTY		= 31; // Пустой емейл
	const FAIL_EMAIL_INCORRECT	= 32; // Емейл некорректен
	const FAIL_EMAIL_REPEAT		= 33; // Уже используется
	
	const FAIL_PASSWORD_EMPTY	= 41; // Пустой пароль
	const FAIL_PASSWORD_SHORT	= 42; // Пустой пароль
	
	const FAIL_CODE_INCORRECT	= 51; // Неверный код 
	
	/**
	 * Максимальное количество регистраций с 1 ip
	 * @var integer
	 */
	const IP_DAY_LIMIT = 20;
	
	public static $config = array (
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
	     * Дополнительные поля.
	     * @var array
	     */
	    'ext_fields'	=> array ()
	);
	
	public static $scheme = array (
		Query::FROM	    => __CLASS__,
		Query::INDEX	=> array (
			array ('email'),
			array ('day', 'ip'),
			array ('code')
		)
	);
	
	public static function checkIp ($ip)
	{
	    Loader::load ('Common_Date');
		$regs = IcEngine::$modelManager->collectionBy (
		    'Registration',
		    Query::instance ()
		    ->where ('day', Common_Date::eraDayNum ())
		    ->where ('ip', $ip)
		);
		
		if ($regs->count () >= self::IP_DAY_LIMIT)
		{
			return self::FAIL_IP_LIMIT;
		}
		
		return self::OK;
	}
	
	/**
	 * 
	 * @param string $email
	 * @param string $password
	 * @param string $ip
	 * @return integer
	 */
	public static function checkData ($email, $password, $ip)
	{
		if (empty ($email))
		{
			return self::FAIL_EMAIL_EMPTY;
		}
		
		if (!filter_var ($email, FILTER_VALIDATE_EMAIL))
		{
		    return self::FAIL_EMAIL_INCORRECT;
		}
		
		if (empty ($password))
		{
			return self::FAIL_PASSWORD_EMPTY;
		}
		
		$user = IcEngine::$modelManager->modelBy (
		    'User',
		    Query::instance ()
		    ->where ('email', $email)
		);
		
		if ($user)
		{
			return self::FAIL_EMAIL_REPEAT;
		}
		
		$reg = IcEngine::$modelManager->modelBy (
		    'Registration',
		    Query::instance ()
		    ->where ('email', $email)
		);
		
		if ($reg)
		{
			return self::FAIL_EMAIL_REPEAT;
		}
		
		return self::checkIp ($ip);
	}
	
	public static function loadConfig ()
	{
        Loader::load ('Config_Php');
        $cfg = new Config_Php ('config/Registration.php');
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
		}
		else
		{
		    Loader::load ('Zend_Exception');
		    throw new Zend_Exception ('Пользователя не существует.');
		}
	}
	
	/**
	 * 
	 * @param stirng $email
	 * @param string $password
	 * @param boolean $send_mail
	 * @param array $exts
	 * @return Registration
	 */
	public static function register ($email, $password, $send_mail = true, array $exts)
	{
		$user = User::create (
		    $email, $password, 
		    self::$config ['autoactive'], $exts);
		
		Loader::load ('Common_Date');
		$registration = new Registration (array (
			'User__id'	=> $user->id,
			'email'		=> $email,
			'time'		=> date ('Y-m-d H:i:s'),
			'ip'		=> Request::ip (),
			'day'		=> Common_Date::eraDayNum (),
			'finished'		=> 0,
			'finishTime'	=> '2000-01-01 00:00:00',
			'code'			=> self::generateUniqueCode ($user->id)
		));
		$registration->save ();
		
		if ($send_mail)
		{
    		Loader::load ('Mail_Message');
    		$message = Mail_Message::create (
    			'user_register', 
    			$email, $email,
    			array (
    				'email'		=> $email,
    				'password'	=> $password,
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
	 * @param string $email
	 * @param string $password
	 * @param string $ip
	 * @param array $exts
	 * 		Дополнительные поля для пользователя
	 * @return integer
	 */
	public static function tryRegister ($email, $password, $ip, array $exts)
	{
	    $result = self::checkData ($email, $password, $ip);
	    
	    if ($result != self::OK)
	    {
	        return $result;
	    }
	    
	    $registration = self::register (
	        $email, $password,
	        self::$config ['sendmail'], $exts);
	    
	    return $registration ? self::OK : self::FAIL_EMAIL_REPEAT;
	}
	
}

Registration::loadConfig ();
Model_Scheme::add ('Registration', Registration::$scheme);