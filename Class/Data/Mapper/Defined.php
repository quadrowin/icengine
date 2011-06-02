<?php
/**
 * 
 * @desc Мэппер для моделей, данные которых перечисленны в самом классе.
 * @author Илья Колесников
 * @package IcEngine
 *
 */
class Data_Mapper_Defined extends Data_Mapper_Abstract
{
	
	/**
	 * @desc
	 * @param Query $query
	 */
	protected function _selectQuery (Query $query)
	{
		$select = $query->getPart (Query::SELECT);
		
		$model_name = reset ($select);
		
		if (is_array ($model_name))
		{
			$model_name = reset ($model_name);
		}
		else
		{
			return new Query_Result (array (
				'source'	=> $source,
				'result'	=> array ()
			));
		}
		
		
		Loader::load ($model_name);
		
		$where = $query->getPart (Query::WHERE);
		
		$part = reset ($where);
		
		$id = $part [Query::VALUE];

		$model = new $model_name;
		
		$rows = $model::$_rows;   
		
		$result = array (array_merge (
			array ($part [Query::WHERE]	=> $id),
			$rows [$id]
		));
		
		return new Query_Result (array (
			'result'	=> $result,	
			'source'	=> $source
		));
	}
	
	protected function _showQuery (Query $query)
	{
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Data_Mapper_Abstract::execute()
	 */
	public function execute (Data_Source_Abstract $source, Query $query, $options = null)
	{
		$method = strtolower ($query->type ());
		
		return call_user_func (
			array ($this, '_' . $method . 'Query'),
			$query
		);
	}
}