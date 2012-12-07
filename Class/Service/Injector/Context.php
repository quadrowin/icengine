<?php

/**
 * Внедрение зависимости через контекст
 * 
 * @author morph
 */
class Service_Injector_Context extends Service_Injector_Abstract
{
    /**
     * @inheritdoc
     */
    public function inject($object, $scheme)
    {
        $services = array();
        foreach ($scheme as $service) {
            $serviceName = isset($service['name']) 
                ? $service['name'] : reset($service);
            $fieldName = reset($service);
            $service = $this->serviceLocator->getService($serviceName);
            $services[$fieldName] = $service;
        }
        $context = new Objective($services);
        return $context;
    }
}