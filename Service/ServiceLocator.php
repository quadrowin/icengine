<?php

namespace IcEngine\Service;

/**
 * Локатор сервисов
 *
 * @author morph
 */
class ServiceLocator
{
    /**
     * Полученные сервисы
     */
    protected static $services = array();

    /**
     * Источник сервисов
     *
     * @var IcEngine\Service\ServiceSource
     */
    protected $source; 
    
    /**
     * Получить сервис
     *
     * @param string $serviceName
     */
    public function getService($serviceName)
    {
        if (!isset(self::$services[$serviceName])) {
            $service = $this->source()->getService($serviceName);
            $this->registerService($serviceName, $service);
        }
        return self::$services[$serviceName];
    }

    /**
     * Получить источник услуг
     *
     * @return Service_Source
     */
    public function getSource()
    {
        return $this->source();
    }

    /**
     * Привести имя класса к стандартному имени сервиса
     * 
     * @param string $name
     * @return string
     */
    public function normalizeName($name)
    {
        $nameParts = explode('_', $name);
        $loweredNameParts = array_map('strtolower', $nameParts);
        $firstParts = reset($loweredNameParts);;
        if (count($loweredNameParts) == 1) {
            return $firstParts;
        }
        array_shift($loweredNameParts);
        $upperedNameParts = array_map('ucfirst', $loweredNameParts);
        return $firstParts . implode('', $upperedNameParts);
    }
    
    /**
     * Регистрация нового сервиса
     *
     * @param string $serviceName
     * @param mixed $service
     */
    public function registerService($serviceName, $service)
    {
        self::$services[$serviceName] = $service;
    }

    /**
     * Изменить источник услуг
     *
     * @param \IcEngine\Service\ServiceSource $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * Получить/созать источник услуг
     *
     * @return \IcEngine\Service\ServiceSource
     */
    protected function source()
    {
        if (!$this->source) {
            $this->source = new IcEngine\Service\ServiceSource();
            $this->source->setLocator($this);
        }
        return $this->source;
    }
}