<?php

/**
 * Сессия пользователя
 * 
 * @author neon
 * @Service("userSession")
 */
class User_Session extends User_Session_Abstract
{
    /**
     * @inheritdoc
     */
    public function getParams()
    {
        return array();
    }
}