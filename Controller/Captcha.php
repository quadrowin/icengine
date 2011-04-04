<?php

class Controller_Captcha extends Controller_Abstract
{
	
	public function getCode ()
	{
		Loader::load ('Helper_Captcha');
		
		$code = Helper_Captcha::generateAutocode ();
		
		$_SESSION [Helper_Captcha::SF_AUTO_CODE] = $code;
		
		$this->_output->send ('data', array (
			'code'	=> $code
		));
		
		$this->_dispatcherIteration->setTemplate (null);
	}
	
}