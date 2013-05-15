<?php

/**
 * Базовый менеджер сущностей
 * 
 * @author morph
 */
class Manager_Simple extends Manager_Abstract
{
    /**
     * Стандратная функция получения эмземпляра по имени
     * 
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        $className = get_class($this);
        $managerPos = strrpos($className, '_Manager');
        $plainName = substr($className, 0, $managerPos);
        $objectClassName = $plainName . '_' . $name;
        $object = new $objectClassName;
        return $object;
    }
}