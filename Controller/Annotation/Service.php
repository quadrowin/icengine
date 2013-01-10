<?php

/**
 * Контроллер для аннотаций типа "Service"
 * 
 * @author morph
 */
class Controller_Annotation_Service extends Controller_Abstract
{
    /**
     * Обновить аннотации
     */
    public function update($data)
    {
        $this->task->setTemplate(null);
        if (!$data) {
            return;
        }
        $services = array();
        foreach ($data as $className => $annotationData) {
            $subData = $annotationData['Service'];
            $serviceName = array_shift($subData['data'][0]);
            $services[$serviceName] = $subData['data'][0];
            $services[$serviceName]['class'] = $className;
            $services[$serviceName]['name'] = $serviceName;
        }
        if (!$services) {
            return;
        }
        $configServices = $this->getService('configManager')->get(
            'Service_Source'
        )->__toArray();
        if ($configServices) {
            foreach ($configServices as $serviceName => $service) {
                if (!isset($services[$serviceName])) {
                    $service['name'] = $serviceName;
                    $services[$serviceName] = $service;
                }
            }
        }
        ksort($services);
        $output = Helper_Code_Generator::fromTemplate(
            'service',
            array (
                'services'  => $services
            )
        );
        $filename = IcEngine::root() . 'Ice/Config/Service/Source.php';
        file_put_contents($filename, $output);
    }
}