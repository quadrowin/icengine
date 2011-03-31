<?php

class Controller_Breadcrumbs extends Controller_Abstract
{
    
    public function index ()
    {
        Loader::load ('Route_Collection');
        $path = new Route_Collection ();
        $route = IcEngine::$application->frontController->getRouter ()->getRoute ();
        while ($route)
        {
            $path->add ($route);
            $route = $route->getParent ();
        }
        
        $this->_output->send (array (
            'path'	=> $path->reverse ()
        ));
    }
    
}