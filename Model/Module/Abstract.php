<?php

class Module_Abstract extends Model_Factory_Delegate
{
    /**
     * Получить путь модуля

     * @return string
     */
    public function path()
    {
        return $this->name . '/';
    }
}