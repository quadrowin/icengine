<?php

/**
 * @desc Событие перед началом рендера из фронт контроллера.
 * @author Юрий
 *
 */
class Message_Before_Render extends Message_Abstract
{
    
    public static function push ($view, array $params = array ())
    {
        IcEngine::$application->messageQueue->push (
        	'Before_Render',
            array_merge (
                $params,
                array ('view'	=> $view)
            )
        );
    }
    
    /**
     * @return View_Render_Abstract
     */
    public function view ()
    {
        return $this->view;
    }
    
}