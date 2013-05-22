<?php

/**
 * Процесс обновления данных сайта
 * 
 * @author morph
 */
class Controller_Deploy extends Controller_Abstract
{
    /**
     * @inheritdoc
     */
    protected $config = array(
        'default'   => array(
            'Annotation/update',
            'Static_Resource/recache'   => array(
                'cityId'    => 1
            ),
            'Redis_Clear/clearContent'
        )
    );
    
    /**
     * Запускает процесс выкладки
     * 
     * @Validator("User_Cli")
     * @Template(null)
     */
    public function index($context, $name = 'default')
    {
        $config = $this->config();
        if (!isset($config[$name])) {
            echo 'Profile not found' . PHP_EOL;
            return;
        }
        echo 'Starting...' . PHP_EOL;
        $i = 1;
        foreach ($config[$name] as $controller => $params) {
            if (is_numeric($controller)) {
                $controller = $params;
                $params = array();
            } else {
                $params = $params->__toArray();
            }
            list($controller, $action) = explode('/', $controller);
            echo '#' . $i . ' ' . $controller . '/' . $action . '...';
            $context->controllerManager->call(
                $controller, $action, $params
            );
            echo ' done. ' . PHP_EOL;
            $i++;
        }
        echo 'All done.' . PHP_EOL;
    }
}