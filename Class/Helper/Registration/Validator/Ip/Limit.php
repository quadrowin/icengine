<?php

class Helper_Registration_Validator_Ip_Limit
{
    
    const IP_LIMIT			= 'ipLimit'; // Лимит на 1 ip
    
    public static function validate ()
	{
	    Loader::load ('Common_Date');
		$regs = IcEngine::$modelManager->collectionBy (
		    'Registration',
		    Query::instance ()
		    ->where ('day', Helper_Date::eraDayNum ())
		    ->where ('ip', Request::ip ())
		);
		
		if ($regs->count () >= Registration::$config ['ip_day_limit'])
		{
			return self::IP_LIMIT;
		}
		
		return Registration::OK;
	}
    
}