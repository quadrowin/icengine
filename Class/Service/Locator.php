<?php

/**
 * Локатор сервисов
 *
 * @author morph
 */
class Service_Locator
{
    /**
     * Полученные сервисы
     */
    protected static $services = array();

    /**
     * Источник сервисов
     *
     * @var Service_Source
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
     * @param Service_Source $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * Получить/созать источник услуг
     *
     * @return Service_Source
     */
    protected function source()
    {
        if (!$this->source) {
            $this->source = new Service_Source;
            $this->source->setLocator($this);
        }
        return $this->source;
    }
}