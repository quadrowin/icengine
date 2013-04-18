<?php

namespace IcEngine\Service;

/**
 * Источник услуг
 *
 * @author morph
 */
class ServiceSource
{
    /**
     * Локатор сервисов источника
     *
     * @var IcEngine\Service\ServiceLocator
     */
    protected $locator;

    /**
     * Сервисы
     *
     * @var array
     */
    protected static $services;

    /**
     * Построить сервис
     *
     * @param string $serviceName
     * @param array $serviceData
     * @return mixed
     */
    protected function buildService($serviceName, $serviceData)
    {
        $className = $serviceData['class'];
        if (!empty($serviceData['source']) || !empty($serviceData['args'])) {
            $args = !empty($serviceData['args']) ? $serviceData['args'] :
                array();
            if ($args) {
                foreach ($args as &$arg) {
                    $arg = $this->getArg($arg);
                }
            }
            if (empty($serviceData['source'])) {
                $reflection = new \ReflectionClass($className);
                return $reflection->newInstanceArgs($args);
            } else {
                if (!empty($serviceData['source'])) {
                    $sourceData = $serviceData['source'];
                    if (!empty($sourceData['name'])) {
                        $source = $this->getService($sourceData['name']);
                        $this->locator->registerService(
                            $sourceData['name'], $source
                        );
                    } else {
                        if (empty($sourceData['isAbstract'])) {
                            $source = new $className;
                        } else {
                            $source = $className;
                        }
                    }
                }
                $method = $sourceData['method'];
                if (empty($serviceData['isStatic'])) {
                    self::$services[$serviceName]['instanceCallback'] = array(
                        array($source, $method), $args
                    );
                }
                return call_user_func_array(array($source, $method), $args);
            }
        } else {
            return new $className;
        }
    }

    /**
     * Получить аргумент
     *
     * @param string $arg
     */
    protected function getArg($arg)
    {
        if ($arg[0] == '$') {
            $argName = substr($arg, 1);
            $arg = $this->getService($arg);
            $this->locator->registerService($argName, $arg);
        }
        return $arg;
    }

    /**
     * Получить локатор сервисов
     *
     * @return IcEngine\Service\ServiceLocator
     */
    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * Получить услугу по имени
     *
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        $service = null;
        if(is_null(self::$services)) {
            $this->loadServices();
        }
        if (!isset(self::$services[$serviceName])) {
            return null;
        }
        $serviceData = self::$services[$serviceName];
        $className = $serviceData['class'];
        if (empty($serviceData['isAbstract'])) {
            $service = $this->buildService($serviceName, $serviceData);
        }
        $instanceCallback = array();
        if (!empty(self::$services[$serviceName]['instanceCallback'])) {
           $instanceCallback = self::$services[$serviceName]['instanceCallback'];
        }
        $state = new IcEngine\Service\ServiceState(
            $service, $className, $instanceCallback
        );
        return $state;
    }

    /**
     * Загрузить конфигурацию сервисов
     */
    protected function loadServices()
    {
        $filename = IcEngine\IcEngine::root() . 'Ice/Config/Service/Source.php';
        if (is_file($filename)) {
            self::$services = include_once($filename);
        } else {
            self::$servcies = array();
        }
    }

    /**
     * Изменить локатор сервисов
     *
     * @param IcEngine\Service\ServiceLocator $locator
     */
    public function setLocator($locator)
    {
        $this->locator = $locator;
    }
}