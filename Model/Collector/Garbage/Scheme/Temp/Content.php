<?php

class Collector_Garbage_Scheme_Temp_Content extends Collector_Garbage_Scheme_Abstract
{
	/**
	 * 
	 * @var integer
	 * @desc Время жизни TempContenta, дней
	 */
	const EXPIRATION_DAYS = 3;
	
	public function process ($config = null)
	{
		if (empty ($config->expirationDays))
		{
			$config->expirationDays = self::EXPIRATION_DAYS;
		}
		
		Loader::load ('Common_Date');	
		
		$day = Common_Date::eraDayNum ();
		
		DDS::execute(
			Query::instance ()
				->delete ()
				->from ('Temp_Content')
				->where ('Temp_Content.day<=?', $day - (int) $config->expirationDays)
		);
	}
}