<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат по фильтрации входных аргументов
 *
 * @author morph
 */
class ControllerManagerDelegeeInputFilter extends 
    ControllerManagerDelegeeAbstract
{
    /**
     * @inheritdoc
     * @see IcEngine\Controller\Manager\ControllerManagerDelegeeAbstract::call
     */
    public function call($controller, $context)
    {
        $controllerManager = $context->getControllerManager();
        $scheme = $controllerManager->annotationManager()
            ->getAnnotation($controller);
        $actionScheme = $scheme->getMethod($context->getAction());
        if (!empty($actionScheme['InputFilter'])) {
            $filterManager = $controllerManager->getService(
                'dataFilterManager'
            );
            $args = $context->getArgs();
            $input = $controller->getInput();
            foreach ($actionScheme['InputFilter'] as $actionArgs) {
                foreach ($actionArgs as $argName => $filters) {
                    if (!isset($args[$argName])) {
                        $args[$argName] = null;
                    }
                    foreach ($filters as $filterName) {
                        $filter = $filterManager->get($filterName);
                        $args[$argName] = $filter->filter($args[$argName]);
                        $input->send($argName, $args[$argName]);
                    }
                }
            }
            $context->setArgs($args);
        }
    }
}