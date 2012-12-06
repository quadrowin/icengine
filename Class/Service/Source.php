<?php

/**
 * Источник услуг
 *
 * @author morph
 */
class Service_Source
{
    /**
     * Менеджер аннотаций
     * 
     * @var Annotation_Manager_Abstract
     */
    protected $annotationManager;
    
    /**
     * Локатор сервисов источника
     *
     * @var Service_Locator
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
        $object = null;
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
                $object = $reflection->newInstanceArgs($args);
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
                $object = call_user_func_array(array($source, $method), $args);
            }
        } else {
            if (!empty($serviceData['isAbstract'])) {
                $reflection = new \ReflectionClass($className);
                $object = $reflection->newInstanceWithoutConstructor();
            } else {
                $object = new $className;
            }
        }
        if ($this->annotationManager) {
            $realObject = $object;
            if ($object instanceof Service_State) {
                $realObject = $object->__object();
            }
            $annotations = $this->annotationManager->getAnnotation($realObject)
                ->getData();
            if (!empty($annotations['properties'])) {
                $properties = $annotations['properties'];
                $reflection = null;
                foreach ($properties as $propertyName => $data) {
                    if (!isset($data['Inject'])) {
                        continue;
                    }
                    if (!$reflection) {
                         $reflection = new \ReflectionClass($className);
                    }
                    $values = array_values($data['Inject']);
                    $serviceName = $values[0];
                    $service = $this->locator->getService($serviceName);
                    $propertyReflection = $reflection->getProperty(
                        $propertyName
                    );
                    $propertyReflection->setAccessible(true);
                    if ($propertyReflection->isStatic()) {
                        $reflection->setStaticPropertyValue(
                            $propertyName, $service
                        );
                    } else {
                        $propertyReflection->setValue($realObject, $service);
                    }
                }
            }
        }
        return $object;
    }

    /**
     * Получить менеджер аннотаций
     * 
     * @return Annotation_Manager_Abstract
     */
    public function getAnnotationManager()
    {
        return $this->annotationManager;
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
     * @return Service_Locator
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
        $state = new Service_State($service, $className, $instanceCallback);
        return $state;
    }

    /**
     * Загрузить конфигурацию сервисов
     */
    protected function loadServices()
    {
        $filename = IcEngine::root() . 'Ice/Config/Service/Source.php';
        if (is_file($filename)) {
            self::$services = include_once($filename);
        } else {
            self::$servcies = array();
        }
    }
    
    /**
     * Менеджер аннотаций
     * 
     * @param Annotation_Manager_Abstract $annotationManager
     */
    public function setAnnotationManager($annotationManager)
    {
        $this->annotationManager = $annotationManager;
    }
    
    /**
     * Изменить локатор сервисов
     *
     * @param Service_Locator $locator
     */
    public function setLocator($locator)
    {
        $this->locator = $locator;
    }
}