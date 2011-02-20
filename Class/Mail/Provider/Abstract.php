<?php

class Mail_Provider_Abstract
{
    
    /**
	 * 
	 * @param array $mails
	 * @param string $message
	 * @param array $config
	 * @return boolean
	 */
	public function send ($mails, $message, $config)
	{
		return false;
	}
	
}