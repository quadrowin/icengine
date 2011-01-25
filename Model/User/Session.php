<?php

class User_Session extends Model
{
	
    /**
     * 
     * @var User_Session
     */
    protected static $_current = null;
	
	/**
	 * 
	 * @param string $session_id
	 * @return User_Session
	 */
	public static function byPhpSessionId ($session_id, $autocreate = true)
	{
		if (empty ($session_id))
		{
		    Loader::load ('Zend_Exception');
			throw new Zend_Exception ('Empty php session id received.');
		}
		
		$session = IcEngine::$modelManager->modelBy (
		    'User_Session',
		    Query::instance ()
		        ->where ('phpSessionId', $session_id)
		);
		
		if (!$session && $autocreate)
		{
    		$session = new User_Session (array (
    			'User__id'		=> 0,
    			'phpSessionId'	=> $session_id,
    			'startTime'	    => date (Helper_Date::UNIX_FORMAT),
    			'lastActive'	=> date (Helper_Date::UNIX_FORMAT),
    			'remoteIp'		=> Request::ip (),
    			'userAgent'	    => substr (getenv ('HTTP_USER_AGENT'), 0, 100)
    		));
    		$session->save ();
		}
		
		return $session;
	}
	
	/**
	 * @return User_Session
	 */
	public static function getCurrent ()
	{
	    return self::$_current;
	}
	
	/**
	 * 
	 * @param User_Session $session
	 */
	public static function setCurrent (User_Session $session)
	{
	    self::$_current = $session;
	}
	
	/**
	 * @param integer $new_user_id [optional]
	 * 		Изменить пользователя.
	 * @return User_Session
	 */
	public function updateSession ($new_user_id = null)
	{
		if (isset ($new_user_id))
		{
			$upd = array (
				'User__id'		=> $new_user_id,
				'lastActive'	=> date (Helper_Date::UNIX_FORMAT)
			);
		}
		else
		{
			$now = date (Helper_Date::UNIX_FORMAT);
			
			// Обновляем сессию не чаще, чем раз в 10 минут.
			// strlen ('YYYY-MM-DD HH:I_:__') = 
			if (strncmp($now, $this->lastActive, 15) == 0)
			{
				return $this;
			}
			
			$upd = array (
				'lastActive'	=> $now
			);
		}
		
	    $this->update ($upd);
		
		return $this;
	}
	
}