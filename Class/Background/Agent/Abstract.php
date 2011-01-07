<?php

abstract class Background_Agent_Abstract
{
    
    /**
     * 
     * @param array $params
     * 		Параметры.
     * 		Будут сохранены и переданы при следующей итерации.
     * @return boolean
     * 		true, если необходимо продолжение процесса.
     * 		Если false, процесс не будет перезапущен.
     */
    abstract public function process (array &$params);
    
    /**
     * Запуск процесса.
     * @return array
     * 		Параметры, с которыми будет запущена первая итерация
     */
    abstract public static function start ();
    
}