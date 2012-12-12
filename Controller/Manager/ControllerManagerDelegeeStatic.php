<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для добавления к действию контроллера статических файлов
 * 
 * @author morph
 */
class ControllerManagerDelegeeStatic extends ControllerManagerDelegeeAbstract
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
        if (!empty($actionScheme['Static'])) {
            $helperViewResource = $controller->getService('helperViewResource');
            foreach ($actionScheme['Static'] as $static) {
                if (empty($static['file'])) {
                    continue;
                }
                $file = $static['file'];
                $type = reset($static);
                $group = !empty($static['group']) ? $static['group'] : null;
                $helperViewResource->append($type, $file, $group);
            }
        }
    }
}