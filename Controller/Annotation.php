<?php

/**
 * Аннотации объектов
 * 
 * @author morph
 */
class Controller_Annotation extends Controller_Abstract
{
    /**
     * Сброк аннотации
     */
    public function flush($name, $context)
    {
        $this->task->setTemplate(null);
        $user = $context->user->getCurrent();
        if ($user->key() >= 0 && !$user->hasRole('editor')) {
            return;
        }
        $config = $context->configManager->get('Data_Provider_Manager');
        $annotationConfig = $config['Annotation'];
        $filename = IcEngine::root() . $annotationConfig['params']['path'] . 
            $name;
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
    
    /**
     * Обновить аннотации
     */
    public function update($path, $context)
    {
        $this->task->setTemplate(null);
        $user = $context->user->getCurrent();
        if ($user->key() >= 0 && !$user->hasRole('editor')) {
            return;
        }
        $classes = array();
        if ($path) {
            $paths = array(IcEngine::root() . $path);
        } else {
            $paths = array_merge(
                IcEngine::getLoader()->getPaths('Class'),
                IcEngine::getLoader()->getPaths('Model'),
                IcEngine::getLoader()->getPaths('Controller')
            );
        }
        foreach ($paths as $path) {
            if (!$path || !is_dir($path)) {
                continue;
            }
            if (strpos($path, 'Class') === false && 
                strpos($path, 'Controller') === false &&
                strpos($path, 'Model') === false) {
                continue;
            }
            ob_start();
            system('find ' . $path . '** | grep .php');
            $content = ob_get_contents();
            ob_end_clean();
            $files = explode(PHP_EOL, $content);
            foreach ($files as $file) {
                if (!$file || !is_file($file)) {
                    continue;
                }
                $content = file_get_contents($file);
                if (strpos($content, 'namespace IcEngine\\') !== false) {
                    continue;
                }
                $matches = array();
                preg_match_all(
                    '#class\s+([A-Z][A-Za-z_0-9]+)#', $content, $matches
                );
                if (empty($matches[1][0])) {
                    continue;
                }
                $classes[] = array(
                    'class' => $matches[1][0],
                    'file'  => $file
                );
            }
        }
        if (!$classes) {
            return;
        }
        $services = array();
        $annotationManager = IcEngine::serviceLocator()->getSource()
            ->getAnnotationManager();
        foreach ($classes as $i => $class) {
            IcEngine::getLoader()->load($class['class']);
            echo '#' . ($i + 1) . ' ' . $class['class'] . ' (' . $class['file'] .  
                ') done.' . PHP_EOL;
            $context->controllerManager->call('Annotation', 'flush', array(
                'name'  => $class['class']
            ));
            $annotation = $annotationManager->getAnnotation($class['class'])
                ->getData();
            $classAnnotation = $annotation['class'];
            if ($classAnnotation && !empty($classAnnotation['Service'])) {
                $serviceAnnotation = $classAnnotation['Service'][0];
                $serviceName = array_shift($serviceAnnotation);
                $data = $serviceAnnotation;
                $data['class'] = $class['class'];
                $data['name'] = $serviceName;
                $services[$serviceName] = $data;
            }
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
        $output = Helper_Code_Generator::fromTemplate (
            'service',
            array (
                'services'  => $services
            )
        );
        $filename = IcEngine::root() . 'Ice/Config/Service/Source.php';
        file_put_contents($filename, $output);
    }
}