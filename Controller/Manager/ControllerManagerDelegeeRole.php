<?php

namespace IcEngine\Controller\Manager;

/**
 * Делигат для определения доступно ли действие контроллера для текущей роли
 * 
 * @author morph
 */
class ControllerManagerDelegeeRole extends ControllerManagerDelegeeAbstract
{
    /**
     * @inheritdoc 
     * @see IcEngine\Controller\Manager\ControllerManagerDelegeeAbstract::call
     */
    public function call($controller, $context)
    {
        $reflection = new \ReflectionClass($controller);
        $controllerManager = $context->getControllerManager();
        $controllerManager->annotationManager()->getSource()
            ->setReflection($reflection);
        $scheme = $controllerManager->annotationManager()
            ->getAnnotation($controller);
        $args = $context->getArgs();
        $user = $args['context']->user->getCurrent();
        $actionScheme = $scheme->getMethod($context->getAction());
        $request = $args['context']->request;
        if (!empty($actionScheme['role'])) {
            if (!$user->hasRole($actionScheme['role'])) {
                if ($request->isAjax() || $request->isPost()) {
                    $controller->getTask()->setIgnore(true);
                } else {
                    $controller->replaceAction('Error', 'accessDenied');
                }
            }
        }
    }
}