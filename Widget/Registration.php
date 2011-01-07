<?php

Loader::load ('Registration');

class Widget_Registration extends Widget_Abstract
{
    
    protected $_result;
    
    protected $_templates = array (
        Registration::OK                    => 'ok',
        
    	Registration::FAIL_IP_LIMIT			=> 'failIpLimit',
    	
    	Registration::FAIL_EMAIL_EMPTY		=> 'emailEmpty',
    	Registration::FAIL_EMAIL_INCORRECT	=> 'emailIncorrect',
    	Registration::FAIL_EMAIL_REPEAT		=> 'emailRepeat',
    	
    	Registration::FAIL_PASSWORD_EMPTY	=> 'passwordEmpty',
    	Registration::FAIL_PASSWORD_SHORT	=> 'passwordShort',
    	
    	Registration::FAIL_CODE_INCORRECT	=> 'codeIncorrect'
    );
    
    public function index ()
    {
        Loader::load ('Registration');
        
        $exts = array ();
        if (Registration::$config ['ext_fields'])
        {
            foreach (Registration::$config ['ext_fields'] as $field => $type)
            {
                $exts [$field] = substr (
                    $this->_input->receive ($field), 0, 200);
            }
        }
        
        $this->_result = Registration::tryRegister (
            $this->_input->receive ('email'),
            $this->_input->receive ('password'),
            Request::ip (),
            $exts
        );
        
        $this->_template = 
            str_replace (array ('_', '::'), '/', __METHOD__) . 
            '/' . 
            $this->_templates [$this->_result] . '.tpl';
        
        $this->_output->send ('result', $this->_result);
    }
    
}