<?php
/**
 * 
 * @desc
 * @author Илья Колесников
 * @package IcEngine
 *
 */
class Controller_Admin_Database extends Controller_Abstract
{
	private function _className ($table, $prefix)
	{
		if ($prefix)
		{
			$table = substr ($table, strlen ($prefix));
		}
		
		$parts = explode ('_', $table);
		
		for ($i = 0, $icount = sizeof ($parts); $i < $icount; $i++)
		{
			$parts [$i] = ucfirst ($parts [$i]);
		}
		
		$class_name = implode ('_', $parts);
		
		return $class_name;
	}
	
	public function __construct ()
	{
		Loader::load ('Helper_Database');
	}
	
	public function index ()
	{
		if (!User::getCurrent ()->isAdmin ())
		{
			//return $this->_helperReturn('Access', 'denied');
		}
		
		$prefix = DDS::modelScheme ()->defaultPrefix;
		$tables = Helper_Database::tables ();
		
		for ($i = 0, $icount = sizeof ($tables); $i < $icount; $i++)
		{
			if (
				!$tables [$i]->Comment || 
				($prefix && strpos ($tables [$i]->Name, $prefix) !== 0)
			)
			{
				unset ($tables [$i]);
			}
		}
		
		$this->_output->send (array (
			'tables'	=> $tables,
		));
	}
	
	public function table ()
	{
		$table = $this->_input->receive ('table');
		
		if (!User::getCurrent ()->isAdmin () || !$table)
		{
			//return $this->_helperReturn('Access', 'denied');
		}

		$prefix = DDS::modelScheme ()->defaultPrefix;
		
		$class_name = $this->_className ($table, $prefix);
		
		if (!Loader::load ($class_name))
		{
			//return $this->_helperReturn('Access', 'denied');
		}
		
		$collection = Model_Collection_Manager::create ($class_name);
		
		if (!$collection->first () || !isset ($collection->first ()->name))
		{
			//return $this->_helperReturn('Access', 'denied');
		}
		
		$this->_output->send (array (
			'collection'	=> $collection,
			'table'			=> $table
		));
	}
	
	public function row ()
	{
		list (
			$table,
			$row_id
		) = $this->_input->receive (
			'table',
			'id'	
		);
		
		if (!$table)
		{
			//return $this->_helperReturn('Access', 'denied');
		}
		
		$prefix = DDS::modelScheme ()->defaultPrefix;
		
		$class_name = $this->_className ($table, $prefix);
		
		if (!Loader::load ($class_name))
		{
			//return $this->_helperReturn('Access', 'denied');
		}
		
		$row = Model_Manager::byKey (
			$class_name,
			$row_id
		);
		
		$fields = Helper_Database::fields (
			substr ($table, strlen ($prefix))
		);
		
		foreach ($fields as $field)
		{
			if (strpos ($field->Field, '__id') !== false)
			{
				$field->Values = Model_Collection_Manager::create (
					substr ($field->Field, 0, -4)
				);
			}
			
			if ($field->Field == 'parentId')
			{
				$field->Values = Model_Collection_Manager::create (
					$class_name
				);
			}
		}
		
		$this->_output->send (array (
			'row'		=> $row,
			'fields'	=> $fields,
			'table'		=> $table
		));
	}
}