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
        $controllerManager = $context->getControllerManager();
        $scheme = $controllerManager->annotationManager()
            ->getAnnotation($controller);
        $user = $controller->getService('user');
        $actionScheme = $scheme->getMethod($context->getAction());
        $request = $controller->getService('request');
        if (!empty($actionScheme['Role'])) {
            $roles = array();
            foreach ($actionScheme['Role'] as $role) {
                $roles[] = reset($role);
            }
            if (!$user->hasRole($roles)) {
                if ($request->isAjax() || $request->isPost()) {
                    $controller->getTask()->setIgnore(true);
                } else {
                    $controller->replaceAction('Error', 'accessDenied');
                }
            }
        }
    }
}