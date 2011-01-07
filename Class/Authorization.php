<?php

class Authorization
{
    
    const LOGIN_FIELD = 'email';
    
    const PASSWORD_FIELD = 'password';
    
    /**
     * 
     * @param string $login
     * @param string $password
     * @return User|null
     */
    public static function findUser ($login, $password)
    {
        return IcEngine::$modelManager->modelBy (
            'User',
            Query::instance ()
            ->where (self::LOGIN_FIELD, $login)
            ->where (self::PASSWORD_FIELD, $password)
            ->where ('active=1')
            ->order (self::LOGIN_FIELD)
            ->limit (1, 0)
        );
    }
    
    /**
     * 
     * @param string $login
     * @param string $password
     * @return User|null
     */
    public static function authorize ($login, $password)
    {
        $user = self::findUser ($login, $password);
        
        if ($user)
        {
            $user->authorize ();
        }
        
        return $user;
    }
    
    public static function logout ()
    {
        User_Session::getCurrent ()->set ('User__id', 0);
        
        Loader::load ('Header');
        Header::redirect ('/');
    }
    
}