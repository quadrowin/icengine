<?php

class Data_Table extends Model
{
	
	/**
	 * 
	 * @param string $alias
	 * @return string
	 */
	public static function getName ($alias)
	{
		return IcEngine::$application->behavior->tableScheme->get ($alias);
	}
	
}