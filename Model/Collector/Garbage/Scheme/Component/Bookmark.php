<?php

class Collector_Garbage_Scheme_Component_Abstract extends Collector_Garbage_Scheme_Abstract
{
	/**
	 * 
	 * @param null|Config_Abstract $config
	 * @return boolean
	 */
	public function process ($config = null)
	{
		$items = $this->_items ($model);
		
		if (!$items)
		{
			return true;
		}
		
		$scheme = DDS::getDataSource ()
			->getDataMapper()
			->getModelScheme ();
		
		$keyField = $scheme->keyField ($model);
		
		for ($i = 0, $icount = count ($items); $i < $icount; $i++)
		{
			DDS::execute (
				Query::instance ()
					->update ($model)
					->values (array (
						'exists'	=> 0
					))
					->where ($keyField . '=?', $items [$i][$keyField])
			);
		}
		
		return true;
	}
}