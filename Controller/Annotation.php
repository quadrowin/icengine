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
}