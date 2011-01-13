<?php

Loader::load ('Registration');

class Widget_Registration extends Widget_Abstract
{
    
    public function index ()
    {
        Loader::load ('Registration');
        
        $data = array ();
        if (Registration::$config ['fields'])
        {
            foreach (Registration::$config ['fields'] as $field => $info)
            {
                if ($info ['value'] == 'input')
                {
                    $data [$field] = substr (
                        $this->_input->receive ($field), 0, 200);
                }
                elseif (is_array ($info ['value']))
                {
                    $data [$field] = call_user_func ($info ['value']);
                }
            }
        }
        
        $result = Registration::tryRegister ($data);
        
        $this->_template = 
            str_replace (array ('_', '::'), '/', __METHOD__) . 
            '/' . 
            $result . '.tpl';
        
        $this->_output->send ('result', $result);
    }
    
}