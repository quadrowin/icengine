<?php

class User_Session extends Model
{
	
    /**
     * 
     * @var User_Session
     */
    protected static $_current = null;
    
	public static $scheme = array (
		Query::FROM	    => __CLASS__,
		Query::INDEX	=> array (
			array ('phpSessionId'),
			array ('User__id')
		)
	);
	
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
    			'startTime'	    => date ('Y-m-d H:i:s'),
    			'lastActive'	=> date ('Y-m-d H:i:s'),
    			'remoteIp'		=> Request::ip (),
    			'userAgent'	    => substr (getenv ('HTTP_USER_AGENT'), 0, 100)
    		));
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
	 * @return User_Session
	 */
	public function updateSession ()
	{
	    return $this->update (array (
	        'User__id'	    => $this->User__id,
	        'lastActive'	=> date ('Y-m-d H:i:s')
	    ));
	}
	
}

Model_Scheme::add ('User_Session', User_Session::$scheme);