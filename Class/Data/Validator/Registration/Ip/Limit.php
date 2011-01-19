<?php

class Data_Validator_Registration_Ip_Limit
{
	
	const FAIL			= 'fail'; // Лимит на 1 ip
	
    public static function validate ()
	{
	    Loader::load ('Helper_Date');
		$regs = IcEngine::$modelManager->collectionBy (
		    'Registration',
		    Query::instance ()
		    ->where ('day', Helper_Date::eraDayNum ())
		    ->where ('ip', Request::ip ())
		);
		
		if ($regs->count () >= Registration::$config ['ip_day_limit'])
		{
			return __CLASS__ . '/' . self::FAIL;
		}
		
		return true;
	}
    
}