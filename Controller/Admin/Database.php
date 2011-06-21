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
	private function __roles ()
	{
		return Helper_Link::linkedItems (
			User::getCurrent (),
			'Acl_Role'
		);
	}
	
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
		Loader::load ('Helper_Data_Source');
	}
	
	public function index ()
	{
		if (!User::getCurrent ()->isAdmin ())
		{
			return $this->replaceAction ('Access', 'denied');
		}
		
		$prefix = Model_Scheme::$defaultPrefix;
		$tables = Helper_Data_Source::tables ();
		
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
		if (!User::getCurrent ()->isAdmin ())
		{
			return $this->replaceAction ('Access', 'denied');
		}
		
		$table = $this->_input->receive ('table');

		$prefix = Model_Scheme::$defaultPrefix;
		
		$class_name = $this->_className ($table, $prefix);
		
		$collection = Model_Collection_Manager::create ($class_name);
		
		$this->_output->send (array (
			'collection'	=> $collection,
			'table'			=> $table
		));
	}
	
	public function row ()
	{
		if (!User::getCurrent ()->isAdmin ())
		{
			return $this->replaceAction ('Access', 'denied');
		}
		
		list (
			$table,
			$row_id
		) = $this->_input->receive (
			'table',
			'id'	
		);
		
		$prefix = Model_Scheme::$defaultPrefix;
		
		$class_name = $this->_className ($table, $prefix);

		$row = Model_Manager::byKey (
			$class_name,
			$row_id
		);
		
		$fields = Helper_Data_Source::fields (
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
	
	public function save ()
	{
		if (!User::getCurrent ()->isAdmin ())
		{
			return $this->replaceAction ('Access', 'denied');
		}
		
		list (
			$table,
			$row_id,
			$fields
		) = $this->_input->receive (
			'table',
			'row_id',
			'fields'
		);
		
		$prefix = Model_Scheme::$defaultPrefix;
		
		$class_name = $this->_className ($table, $prefix);
		
		/*
		 * @var Model $row
		 */
		$row = Model_Manager::byKey (
			$class_name,
			$row_id
		);
		
		if ($row)
		{
			$row->update ($fields);
		}
		else
		{
			$row->set ($fields);
			$row->save ();
		}
		
		Helper_Header::redirect ('/cp/table/' . $table . '/');
	}
	
	public function delete ()
	{
		if (!User::getCurrent ()->isAdmin ())
		{
			return $this->replaceAction ('Access', 'denied');
		}
		
		list (
			$table,
			$row_id
		) = $this->_input->receive (
			'table',
			'row_id'
		);
		
		$prefix = Model_Scheme::$defaultPrefix;
		
		$class_name = $this->_className ($table, $prefix);
		
		/*
		 * @var Model $row
		 */
		$row = Model_Manager::byKey (
			$class_name,
			$row_id
		);
		
		if ($row)
		{
			$row->delete ();
		}
		
		Helper_Header::redirect ('/cp/table/' . $table . '/');
	}
}