<?php

Loader::load ('Registration');

class Widget_Registration extends Widget_Abstract
{
    
    protected $_result;
    
    public function index ()
    {
        Loader::load ('Registration');
        
        $data = array ();
        if (Registration::$config ['fields'])
        {
            foreach (Registration::$config ['fields'] as $field => $type)
            {
                $data [$field] = substr (
                    $this->_input->receive ($field), 0, 200);
            }
        }
        
        $this->_result = Registration::tryRegister ($data);
        
        $this->_template = 
            str_replace (array ('_', '::'), '/', __METHOD__) . 
            '/' . 
            $this->_result . '.tpl';
        
        $this->_output->send ('result', $this->_result);
    }
    
}