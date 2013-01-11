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
            $serviceData = $service;
            if (isset($service['name'])) {
                $serviceName = $service['name'];
                $fieldName = reset($service);
                $serviceData = array($fieldName => $serviceName);
            }
            foreach ($service as $serviceName => $fieldName) {
                $fetchedService = $this->serviceLocator->getService(
                    $serviceName
                );
                $services[$fieldName] = $fetchedService;
            }
        }
        $context = new Objective($services);
        return $context;
    }
}