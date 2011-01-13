<?php

class Helper_Registration_Validator_Abstract
{
    
    /**
     * Проверка данных регистрации.
     * 
     * @param array $data
     * 		Все данные
     * @param string $name
     * 		Название валидатора как оно указано в конфиге.
     * @return mixed
     * 		Должно вернуть Registration::OK в случае успеха,
     * 		либо код ошибки.
     */
    public static function validate (stdClass $data, $name)
    {
        return Registration::OK;
    }
    
}