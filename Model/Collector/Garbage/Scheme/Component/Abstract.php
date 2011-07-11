<?php

class Collector_Garbage_Scheme_Component_Abstract extends Collector_Garbage_Scheme_Abstract
{
	/**
	 * 
	 * @param string $model
	 * @return array
	 */
	protected function _items ($model)
	{		
		$tables = DDS::execute (
			Query::instance ()
				->select ('table')
				->distinct (true)
				->from ($model)
			)
			->getResult ()
			->asTable ();
		
		if (!$tables)
		{
			return array ();
		}
		
		$scheme = DDS::getDataSource ()
			->getDataMapper()
			->getModelScheme ();
		
		$result = array ();
			
		for ($i = 0, $icount = count ($tables); $i < $icount; $i++)
		{
			$table = $tables [$i]['table'];
			$keyField = $scheme->keyField ($table);
			
			$items = DDS::execute (
				Query::instance()
					->select (array (
						$table 	=> array ('uid' => $keyField),
						$model	=> array ($scheme->keyField ($model))
					))
					->from ($table)
					->leftJoin (
						$model,
						$model . '.' . $scheme->keyField ($model) . 
							'= ' . $table. '.' . $keyField
					)
					->where ($model . '.table=?', $table)
					->where ('ISNULL(' . $table . '.uid)')
				)
				->getResult ()
				->asTable ();
			
			$result = array_merge ($result,  $items);
		}
		
		return $result;
	}
	
	/**
	 * 
	 * @param string $model
	 * @param null|Config_Abstract $config
	 * @return boolean
	 */
	protected function _process ($model, $config = null)
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
					->delete ()
					->from ($model)
					->where ($keyField . '=?', $items [$i][$keyField])
			);
		}
		
		return true;
	}
	
	public function name ()
	{
		return substr (__CLASS__, 25);
	}
	
	/**
	 * 
	 * @param null|Config_Abstract $config
	 * @return boolean
	 */
	public function process ($config = null)
	{
		return $this->_process ($this->name (), $config);
	}
}