<?php

/**
 *
 * Проверка текущего пароля пользователя
 * @author Юрий
 *
 */
class Data_Validator_Current_User_Password extends Data_Validator_Abstract
{

    const INVALID = 'invalid';

    public function validate($data)
    {
        $currentUser = IcEngine::getServiceLocator()
            ->getService('user')
            ->getCurrent();
        if ($currentUser->key() <= 0
            || $data != $currentUser->password
        ) {
            return __CLASS__ . '/' . self::INVALID;
        }

        return true;
    }

}