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
        foreach ($scheme as $serviceName) {
            $service = $this->serviceLocator->getService($serviceName);
            $services[$serviceName] = $service;
        }
        $context = new Objective($services);
        return $context;
    }
}