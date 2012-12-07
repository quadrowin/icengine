<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для изменения внутреннего контекста вызова контроллера
 * 
 * @author morph
 */
class ControllerManagerDelegeeContext extends ControllerManagerDelegeeAbstract
{
    /**
     * @inheritdoc 
     * @see IcEngine\Controller\Manager\ControllerManagerDelegeeAbstract::call
     */
    public function call($controller, $context)
    {
        if (!$controller->hasInjections()) {
            return false;
        }
        $reflection = new \ReflectionClass($controller);
        $controllerManager = $context->getControllerManager();
        $controllerManager->annotationManager()->getSource()
            ->setReflection($reflection);
        $scheme = $controllerManager->annotationManager()
            ->getAnnotation($controller);
        $params = $context->getArgs();
        $actionScheme = $scheme->getMethod($context->getAction());
        if (!empty($actionScheme['Context'])) {
            $actionContext = $controllerManager->serviceInjector()->inject(
                null, $actionScheme['Context']
            );
            $params['context'] = isset($params['context'])
                ? $params['context']->merge($actionContext)
                : $actionContext;
        }
        $context->setArgs($params);
    }
}