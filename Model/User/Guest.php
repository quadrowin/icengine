<?php

class User_Guest extends User
{
    
    protected static $_instance;
    
    protected function _afterConstruct ()
    {
        $this->_loaded = true;
    }
    
    /**
     * @return User_Guest
     */
    public static function getInstance ()
    {
        if (!self::$_instance)
        {
            self::$_instance = new self (array (
                'id'	    => 0,
                'active'	=> 1,
                'name'		=> '',
                'email'	    => '',
                'password'	=> ''
            ));
        }
        return self::$_instance;
    }
    
    public function modelName ()
    {
        return 'User';
    }
    
}

IcEngine::$modelManager->getResourceManager ()->set (
    'Model',
    User_Guest::getInstance ()->resourceKey (),
    User_Guest::getInstance ()
);