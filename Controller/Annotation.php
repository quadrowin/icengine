<?php

/**
 * Аннотации объектов
 *
 * @author morph
 */
class Controller_Annotation extends Controller_Abstract
{
    /**
     * @inheritdoc
     */
    protected $config = array(
        'class'             => array(
            'Orm', 'Service', 'Override', 'AutoResync'
        ),
        'methods'           => array(
            'Route', 'RouteImport', 'Cache', 'Schedule'
        ),
        'properties'        => array(
            'Service'
        )
    );

    /**
     * Сброк аннотации
     * 
     * @Template(null)
     * @Validator("User_Cli")
     */
    public function flush($name, $context)
    {
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
     * 
     * @Template(null)
     * @Validator("User_Cli")
     */
    public function update($path, $verbose, $author, $context)
    {
        $helperAnnotationUpdate = $this->getService('helperAnnotationUpdate');
        $classes = $helperAnnotationUpdate->getClasses($path);
        $delegees = $this->config();
        $delegeeData = array();
        $loader = IcEngine::getLoader();
        foreach ($classes as $i => $class) {
            $loader->load($class['class']);
            if ($verbose) {
                echo '#' . ($i + 1) . ' ' . $class['class'] .
                    ' (' . $class['file'] . ') done.' . PHP_EOL;
            }
            $className = $class['class'];
            $context->controllerManager->call('Annotation', 'flush', array(
                'name'  => $class['class']
            ));
            $delegeeData = array_merge(
                $delegeeData, $helperAnnotationUpdate->getDelegees(
                    $delegees, $className
                )
            );
        }
        foreach ($delegeeData as $delegeeName => $data) {
            $controllerName = 'Annotation_' . $delegeeName;
            $context->controllerManager->call(
                $controllerName, 'update', array(
                    'data'      => $data,
                    'author'    => $author
                )
            );
        }
    }
}