<?php

/**
 * 
 * @package IcEngine
 *
 */
abstract class Background_Agent_Abstract
{
    
    /**
     * @desc Процесс
     * @param array $params
     * 		Параметры.
     * 		Будут сохранены и переданы при следующей итерации.
     * @return boolean
     * 		true, если необходимо продолжение процесса.
     * 		Если false, процесс не будет перезапущен.
     */
    abstract public function process (array &$params);
    
    /**
     * @desc Запуск процесса.
     * @return array
     * 		Параметры, с которыми будет запущена первая итерация
     */
    abstract public static function start ();
    
}