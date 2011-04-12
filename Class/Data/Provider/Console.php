<?php

class Data_Provider_Console extends Data_Provider_Abstract
{
    
    /**
     * 
     * @var array
     */
    protected $_args;
    
    public function __construct (array $args)
    {
        foreach ($args as $arg)
        {
            $p = strpos ($arg, '=');
            if ($p)
            {
                $key = substr ($arg, 0, $p);
                $this->_args [$key] = substr ($arg, $p + 1);
            }
        }
    }
    
	public function get ($key, $plain = false)
	{
		return isset ($this->_args [$key]) ? $this->_args [$key] : null;
	}
	
	public function set ($key, $value, $expiration = 0, $tags = array ())
	{
		echo "$key => $value\n";
	}
    
}