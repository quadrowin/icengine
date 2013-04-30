<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат по фильтрации входных аргументов
 *
 * @author morph
 */
class ControllerManagerDelegeeValidator extends ControllerManagerDelegeeAbstract
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
            $filterManager = $controllerManager->getService('filterManager');
            $args = $context->getArgs();
            foreach ($actionScheme['InputFilter'] as $actionArgs) {
                foreach ($actionArgs as $argName => $filters) {
                    if (!isset($args[$argName])) {
                        $args[$argName] = null;
                    }
                    foreach ($filters as $filterName) {
                        $filter = $filterManager->get($filterName);
                        $args[$argName] = $filter->filter($args[$argName]);
                    }
                }
            }
            $context->setArgs($args);
        }
    }
}