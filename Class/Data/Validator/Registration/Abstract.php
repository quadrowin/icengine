<?php

class Data_Validator_Registration_Abstract
{
    
    /**
     * Проверка данных регистрации.
     * 
     * @param array $data
     * 		Все данные.
     * @param string $name
     * 		Название валидатора как оно указано в конфиге.
     * @param array $info
     * 		Параметры поля.
     * @return mixed
     * 		Должно вернуть true в случае успеха,
     * 		либо код ошибки.
     */
    public static function validate (stdClass $data, $name, array $info)
    {
        return true;
    }
    
}