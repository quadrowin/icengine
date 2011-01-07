<?php

class Collector_Garbage_Scheme_Removal extends Collector_Garbage_Scheme_Abstract
{
	/**
	 * 
	 * @var integer
	 * @desc Время жизни Removal, дней
	 */
	const EXPIRATION_DAYS = 3;
	
	public function process ($config = null)
	{
		Loader::load ('Removal_Collection');
		
		$collection = new Removal_Collection ();
		$removal = $collection->items ();
		
		if (!$removal)
		{
			return true;
		}
		
		Loader::load ('Common_Date');	
		
		$day = Common_Date::eraDayNum ();
		
		for ($i = 0, $icount = count ($removal); $i < $icount; $i++)
		{
			$experationDays = !empty ($config->{$removal [$i]->table}) ?
				$config->{$removal [$i]->table}	: self::EXPIRATION_DAYS;
				
			if ($removal [$i]->day <= ($day - $experationDays))
			{
				$removal [$i]->delete ();
			}
		}
	}
}